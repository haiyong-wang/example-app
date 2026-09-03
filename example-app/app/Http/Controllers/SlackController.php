<?php

namespace App\Http\Controllers;

use App\Models\SlackSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 摸鱼时长统计控制器（自动记录在线时长）
 *
 * 前端周期性发送心跳（heartbeat），后端据此自动维护"在线会话"：
 *  - 无活跃会话 → 开启新会话
 *  - 有活跃会话 → 刷新最后活跃时间
 *  - 有超时会话 → 自动结算上一条，再开启新会话
 *
 * 提供：
 *  - 统计页面（今日累计 + 当前正在进行的实时时长）
 *  - 心跳接口（自动计时）
 *  - 实时时长 JSON（供页面轮询校准）
 *
 * @package App\Http\Controllers
 */
class SlackController extends Controller
{
    /**
     * 统计页面
     *
     * GET /slack
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // 今天范围内的所有摸鱼记录
        $sessions = $user->slackSessions()
            ->whereBetween('started_at', [
                now()->startOfDay()->toDateTimeString(),
                now()->endOfDay()->toDateTimeString(),
            ])
            ->orderBy('started_at')
            ->get();

        // 当前活跃（进行中且未超时）的会话
        $running = $user->activeSlackSession();
        if ($running && $running->last_beat_at
            && now()->diffInSeconds($running->last_beat_at) > SlackSession::OFFLINE_THRESHOLD_SECONDS) {
            // 已超时，视为当前没有活跃会话，但该条仍应计入历史
            $running = null;
        }

        // 今天累计摸鱼秒数
        $todaySeconds = $sessions->reduce(function ($carry, $s) {
            return $carry + $s->secondsElapsed();
        }, 0);

        // 已完成（已结算）的摸鱼次数
        $finishedCount = $sessions->filter(function ($s) {
            return !$s->isRunning();
        })->count();

        // 最近 7 天每天摸鱼秒数（画小柱状用）
        $last7 = $this->lastSevenDaysSeconds($user);

        return view('slack.index', [
            'running'       => $running,          // SlackSession|null
            'todaySeconds'  => $todaySeconds,     // int
            'finishedCount' => $finishedCount,    // int
            'sessionCount'  => $sessions->count(),// int
            'last7'         => $last7,            // [['date'=>, 'seconds'=>]]
        ]);
    }

    /**
     * 心跳接口：前端周期性调用，自动维护在线会话
     *
     * POST /slack/heartbeat
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function heartbeat(Request $request)
    {
        $user = Auth::user();

        // 当前"进行中"的会话（可能有，且可能是超时的）
        $open = $user->activeSlackSession();

        if ($open) {
            $lastBeat = $open->last_beat_at;

            // 若上次心跳距今未超阈值 => 一直在线上，仅刷新活跃时间
            if ($lastBeat && now()->diffInSeconds($lastBeat) <= SlackSession::OFFLINE_THRESHOLD_SECONDS) {
                $open->forceFill(['last_beat_at' => now()])->save();
            } else {
                // 已超时 => 说明中途离线了：先按最后活跃时间结算上一条，再开新会话
                $open->forceFill([
                    'ended_at'     => $lastBeat ?? now(), // 只算到最后活跃那一刻，避免计入离线空档
                    'last_beat_at' => $lastBeat,
                ])->save();

                $open = $this->startSession($user);
            }
        } else {
            // 没有任何进行中会话 => 直接开新会话
            $open = $this->startSession($user);
        }

        return response()->json([
            'ok'             => true,
            'running'        => true,
            'running_seconds'=> $open->secondsElapsed(),
            'today_seconds'  => $this->todaySeconds($user),
            'now'            => now()->timestamp,
        ]);
    }

    /**
     * 实时统计 JSON（供页面轮询校准"当前已摸鱼时长"）
     *
     * GET /slack/status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        $user = Auth::user();

        $running = $user->activeSlackSession();
        if ($running && $running->last_beat_at
            && now()->diffInSeconds($running->last_beat_at) > SlackSession::OFFLINE_THRESHOLD_SECONDS) {
            $running = null;
        }

        return response()->json([
            'running'          => $running ? true : false,
            'running_seconds'  => $running ? $running->secondsElapsed() : 0,
            'today_seconds'    => $this->todaySeconds($user),
            'today_text'       => self::formatSeconds($this->todaySeconds($user)),
            'now'              => now()->timestamp,
        ]);
    }

    /**
     * 开启一个新的在线会话
     *
     * @param  \App\Models\User  $user
     * @return \App\Models\SlackSession
     */
    protected function startSession($user)
    {
        return SlackSession::create([
            'user_id'     => $user->id,
            'started_at'  => now(),
            'ended_at'    => null,
            'last_beat_at'=> now(),
        ]);
    }

    /**
     * 计算用户今天累计摸鱼秒数
     *
     * @param  \App\Models\User  $user
     * @return int
     */
    protected function todaySeconds($user)
    {
        return $user->slackSessions()
            ->whereBetween('started_at', [
                now()->startOfDay()->toDateTimeString(),
                now()->endOfDay()->toDateTimeString(),
            ])
            ->get()
            ->reduce(function ($carry, $s) {
                return $carry + $s->secondsElapsed();
            }, 0);
    }

    /**
     * 获取最近 7 天（含今天）的每天摸鱼秒数
     *
     * @param  \App\Models\User  $user
     * @return array<int, array{date: string, seconds: int}>
     */
    protected function lastSevenDaysSeconds($user)
    {
        $start = now()->startOfDay()->subDays(6);

        $rows = SlackSession::where('user_id', $user->id)
            // 包含已结算（结束时间在统计起点之后）以及仍在进行中的会话
            ->where(function ($q) use ($start) {
                $q->whereNull('ended_at')
                  ->orWhere('ended_at', '>=', $start->toDateTimeString());
            })
            ->orderBy('started_at')
            ->get();

        $perDay = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i);
            $perDay[$day->format('Y-m-d')] = 0;
        }

        foreach ($rows as $session) {
            // 对每一条（可能跨天）的会话，逐日累计到对应日期；
            // 进行中会话以当前时间作为上界
            $from = $session->started_at;
            $to   = $session->ended_at ?? now();

            if ($to->lt($start)) {
                continue;
            }
            if ($from->lt($start)) {
                $from = $start->copy();
            }

            $cursor = $from->copy()->startOfDay();
            while ($cursor->lte($to)) {
                $dayKey = $cursor->format('Y-m-d');
                $dayStart = $cursor->copy();
                $dayEnd   = $cursor->copy()->endOfDay();

                $segFrom = $from->gt($dayStart) ? $from : $dayStart;
                $segTo   = $to->lt($dayEnd) ? $to : $dayEnd;

                if (isset($perDay[$dayKey])) {
                    $perDay[$dayKey] += max(0, $segTo->diffInSeconds($segFrom));
                }

                $cursor->addDay();
            }
        }

        $result = [];
        foreach ($perDay as $date => $seconds) {
            $result[] = ['date' => $date, 'seconds' => $seconds];
        }

        return $result;
    }

    /**
     * 秒数格式化为 时分秒 / 分秒（静态，供 Blade 模板直接调用）
     *
     * @param  int  $seconds
     * @return string
     */
    public static function formatSeconds($seconds)
    {
        $seconds = (int) max(0, $seconds);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        if ($h > 0) {
            return sprintf('%d 小时 %02d 分 %02d 秒', $h, $m, $s);
        }
        if ($m > 0) {
            return sprintf('%d 分 %02d 秒', $m, $s);
        }
        return sprintf('%d 秒', $s);
    }
}
