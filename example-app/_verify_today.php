<?php
// 临时验证脚本：确认今日累计包含进行中会话，并随 now() 增长
$s = App\Models\SlackSession::find(4);
if (!$s) { echo "no session #4\n"; exit; }
$today = $s->user->slackSessions()
    ->whereBetween('started_at', [
        now()->startOfDay()->toDateTimeString(),
        now()->endOfDay()->toDateTimeString(),
    ])
    ->get()
    ->reduce(function ($c, $x) {
        return $c + $x->secondsElapsed();
    }, 0);
echo 'today total secs: ' . $today . PHP_EOL;
echo 'today_text: ' . App\Http\Controllers\SlackController::formatSeconds($today) . PHP_EOL;
// 进行中会话单条
echo 'session #4 secs: ' . $s->secondsElapsed() . PHP_EOL;
