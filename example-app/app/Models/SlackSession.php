<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 摸鱼会话模型
 *
 * 由前端周期性心跳驱动"自动记录在线时长"：
 * 心跳到来时若没有进行中的会话则新建一条；若已有会话则刷新 last_beat_at；
 * 若距上次心跳超过离线阈值，则自动结束上一条并开启新会话。
 *
 * @package App\Models
 */
class SlackSession extends Model
{
    use HasFactory;

    /**
     * 距离上次心跳超过该秒数，判定为离线（会前段自动结算）
     */
    const OFFLINE_THRESHOLD_SECONDS = 300; // 5 分钟

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'started_at',
        'ended_at',
        'last_beat_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at'   => 'datetime',
        'ended_at'     => 'datetime',
        'last_beat_at' => 'datetime',
    ];

    /**
     * 归属用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 本次会话是否仍在进行中
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->ended_at === null;
    }

    /**
     * 结算本次会话：写入结束时间（已结束则忽略）
     *
     * @return $this
     */
    public function close()
    {
        if (!$this->isRunning()) {
            return $this;
        }
        $this->forceFill(['ended_at' => now()])->save();
        return $this;
    }

    /**
     * 获取本次会话实际累计的秒数。
     *
     * 已结束：ended_at - started_at（固定值，不会再变）；
     * 进行中：以当前时间 now() 为上界——"进行中"表示用户此刻被判定仍在线，
     *         因此实时累计应算到"现在"。
     *         （last_beat_at 只用于判断会话是否超时离线，不用于计算本字段）
     *
     * @return int
     */
    public function secondsElapsed()
    {
        $anchor = $this->ended_at ?? now();
        // Carbon 的 diffInSeconds 在方向相反（结束时间早于开始时间）时也会返回正值绝对值，
        // 因此不能依赖 max(0, ...) 拦截"反向时长"。必须显式判断结束时间不早于开始时间，
        // 否则会产生虚增的非法时长（例如结算异常把 ended_at 写到 started_at 之前）。
        if ($anchor->lte($this->started_at)) {
            return 0;
        }
        return $anchor->diffInSeconds($this->started_at);
    }
}
