@php
    // 是否有正在进行的会话（进行中且未超时）
    $isRunning = $running ? true : false;
    // 供 JS 初始化显示的已流逝秒数（首次由后端兜底）
    $initRunningSeconds = $running ? $running->secondsElapsed() : 0;
    $initTodaySeconds   = $todaySeconds;
    // 心跳 / 轮询间隔（秒）
    $heartbeatInterval = 60;
    $offlineThreshold = \App\Models\SlackSession::OFFLINE_THRESHOLD_SECONDS;
@endphp
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>摸鱼时长统计 - {{ config('app.name', '憨憨专属摸鱼网站') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
            background: #f5f6fa;
            color: #2c3e50;
            font-size: 14px;
            min-height: 100vh;
        }
        a { text-decoration: none; color: inherit; }

        .layout { display: flex; min-height: 100vh; }

        /* 左侧菜单 */
        .sidebar { width: 200px; background: #fff; border-right: 1px solid #ebeef5; flex-shrink: 0; }
        .sidebar-logo { padding: 18px 20px; border-bottom: 1px solid #ebeef5; font-weight: bold; font-size: 15px; color: #303133; }
        .menu { padding: 8px 0; }
        .menu-item { display: block; padding: 12px 20px; color: #303133; cursor: pointer; border-left: 3px solid transparent; }
        .menu-item:hover { background: #f5f7fa; }
        .menu-item.active { background: #ecf5ff; color: #409eff; border-left-color: #409eff; }

        /* 右侧主体 */
        .main { flex: 1; min-width: 0; }

        /* 顶部 */
        .topbar { background: #fff; padding: 10px 20px; border-bottom: 1px solid #ebeef5; font-size: 13px; color: #606266; }
        .topbar .sep { color: #c0c4cc; margin: 0 6px; }

        .tab-content { padding: 20px; }

        /* 卡片 */
        .card { background: #fff; border-radius: 8px; margin-bottom: 16px; border: 1px solid #ebeef5; }
        .card-title { padding: 14px 20px; border-bottom: 1px solid #ebeef5; font-weight: 500; font-size: 14px; color: #303133; position: relative; padding-left: 28px; }
        .card-title::before { content: ""; position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 4px; height: 14px; background: #e89b26; border-radius: 2px; }
        .card-body { padding: 20px; }

        /* 主控区 */
        .slack-hero {
            background: linear-gradient(135deg, #f2b84b 0%, #e89b26 60%, #d9822b 100%);
            border-radius: 10px;
            padding: 28px;
            margin-bottom: 16px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        .live-box { flex: 1; min-width: 260px; }
        .live-label { font-size: 13px; opacity: .92; letter-spacing: 1px; }
        .live-time {
            font-size: 52px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            margin: 6px 0 4px;
            line-height: 1.1;
        }
        .live-sub { font-size: 12px; opacity: .9; }
        .live-state { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; margin-bottom: 8px; }
        .dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: #fff; animation: blink 1.2s infinite;
        }
        .dot.paused { background: rgba(255,255,255,.55); animation: none; }
        @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: .35; } }

        .stats-box { display: flex; gap: 30px; flex-wrap: wrap; }
        .stat { text-align: center; }
        .stat .k { font-size: 12px; opacity: .9; }
        .stat .v { font-size: 26px; font-weight: 700; margin-top: 2px; font-variant-numeric: tabular-nums; }

        /* 自动计时说明条 */
        .auto-tip {
            background: rgba(255,255,255,.92);
            color: #d9822b;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 13px;
            margin-top: 16px;
            width: 100%;
        }

        /* 信息小格 */
        .mini-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
        .mini-item { background: #fafbfc; border: 1px solid #ebeef5; border-radius: 8px; padding: 12px 14px; }
        .mini-item .k { font-size: 12px; color: #909399; }
        .mini-item .v { font-size: 18px; font-weight: 600; color: #303133; margin-top: 4px; font-variant-numeric: tabular-nums; }

        /* 会话列表 */
        .session-table { width: 100%; border-collapse: collapse; }
        .session-table th, .session-table td { text-align: left; padding: 10px 12px; font-size: 13px; }
        .session-table th { color: #909399; font-weight: 500; border-bottom: 1px solid #ebeef5; }
        .session-table td { border-bottom: 1px solid #f5f7fa; color: #606266; }
        .tag-run { display: inline-block; padding: 1px 8px; border-radius: 8px; font-size: 12px; background: #fdf6ec; color: #e6a23c; }
        .tag-closed { display: inline-block; padding: 1px 8px; border-radius: 8px; font-size: 12px; background: #ecf5ff; color: #409eff; }
        .empty { text-align: center; color: #c0c4cc; padding: 30px 0; font-size: 13px; }

        /* 近 7 天柱状图 */
        .bars { display: flex; align-items: flex-end; gap: 14px; height: 160px; padding-top: 10px; }
        .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; height: 100%; justify-content: flex-end; }
        .bar-val { font-size: 12px; color: #909399; font-variant-numeric: tabular-nums; }
        .bar { width: 100%; max-width: 46px; background: linear-gradient(180deg, #f2b84b, #e89b26); border-radius: 4px 4px 0 0; min-height: 2px; }
        .bar.zero { background: #eceff4; }
        .bar-date { font-size: 12px; color: #909399; }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
            .live-time { font-size: 40px; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'slack'])

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            摸鱼时长
            <span class="sep">/</span>
            自动统计
        </div>

        <div class="tab-content">

            <!-- 主控横幅 -->
            <div class="slack-hero">
                <div class="live-box">
                    <div class="live-state">
                        <span class="dot {{ $isRunning ? '' : 'paused' }}" id="stateDot"></span>
                        <span id="stateText">{{ $isRunning ? '正在统计中' : '等待心跳开启计时' }}</span>
                    </div>
                    <div class="live-time" id="liveTime">00:00:00</div>
                    <div class="live-sub" id="liveSub">停留在本页面时，系统会自动累计你的在线摸鱼时长</div>
                </div>

                <div class="stats-box">
                    <div class="stat">
                        <div class="k">今日累计摸鱼</div>
                        <div class="v" id="todayTotal">--:--:--</div>
                    </div>
                    <div class="stat">
                        <div class="k">今日已结算场次</div>
                        <div class="v">{{ $finishedCount }}</div>
                    </div>
                </div>

                <div class="auto-tip">
                    💡 已开启<strong>自动记录</strong>：不用点开始/结束，只要你停留在这个页面，每约 {{ $heartbeatInterval }} 秒会自动上报一次；
                    若超过约 {{ round($offlineThreshold / 60) }} 分钟没有活动（离开/关页面），计时会自动结算分段，不会一直虚增。
                </div>
            </div>

            <!-- 信息格 -->
            <div class="card">
                <div class="card-title">今日概况</div>
                <div class="card-body">
                    <div class="mini-grid">
                        <div class="mini-item">
                            <div class="k">今日已摸鱼</div>
                            <div class="v" id="todayMini">--:--:--</div>
                        </div>
                        <div class="mini-item">
                            <div class="k">本段正在进行</div>
                            <div class="v" id="currentMini">00:00:00</div>
                        </div>
                        <div class="mini-item">
                            <div class="k">今日分段数</div>
                            <div class="v">{{ $sessionCount }} 段</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 今日会话明细 -->
            <div class="card">
                <div class="card-title">今日在线分段明细</div>
                <div class="card-body" style="padding:0">
                    @php
                        $todaySessions = \App\Models\SlackSession::where('user_id', auth()->id())
                            ->whereBetween('started_at', [
                                now()->startOfDay()->toDateTimeString(),
                                now()->endOfDay()->toDateTimeString(),
                            ])
                            ->orderByDesc('started_at')
                            ->get();
                    @endphp
                    @if ($todaySessions->isEmpty())
                        <div class="empty">今天还没有在线摸鱼记录，停留在本页片刻后这里会自动生成 🐟</div>
                    @else
                        <table class="session-table">
                            <thead>
                                <tr>
                                    <th>开始时间</th>
                                    <th>结束时间</th>
                                    <th>持续时长</th>
                                    <th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($todaySessions as $session)
                                    <tr>
                                        <td>{{ $session->started_at->format('H:i:s') }}</td>
                                        <td>
                                            {{ $session->isRunning() ? '—' : $session->ended_at->format('H:i:s') }}
                                        </td>
                                        <td>{{ \App\Http\Controllers\SlackController::formatSeconds($session->secondsElapsed()) }}</td>
                                        <td>
                                            @if ($session->isRunning())
                                                <span class="tag-run">进行中</span>
                                            @else
                                                <span class="tag-closed">已结算</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <!-- 近 7 天趋势 -->
            <div class="card">
                <div class="card-title">近 7 天摸鱼趋势</div>
                <div class="card-body">
                    @php
                        $max = max(1, collect($last7)->max('seconds'));
                    @endphp
                    <div class="bars">
                        @foreach ($last7 as $day)
                            @php
                                $h = round($day['seconds'] / max(1, $max) * 100);
                                $label = $day['seconds'] > 0
                                    ? \App\Http\Controllers\SlackController::formatSeconds($day['seconds'])
                                    : '0';
                                $date = \Illuminate\Support\Carbon::parse($day['date']);
                                $week = ['周日','周一','周二','周三','周四','周五','周六'][(int) $date->dayOfWeek];
                            @endphp
                            <div class="bar-col">
                                <div class="bar-val">{{ $label }}</div>
                                <div class="bar {{ $day['seconds'] === 0 ? 'zero' : '' }}" style="height: {{ max(2, $h) }}%"></div>
                                <div class="bar-date">{{ $date->format('m/d') }} {{ $week }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
(function () {
    // 后端兜底初值（DB 里当前累计秒数 + 进行中已流逝秒数）
    var running = {{ $isRunning ? 'true' : 'false' }};
    var runningSeconds = {{ $initRunningSeconds }};
    var todaySeconds = {{ $initTodaySeconds }};

    // 心跳用 CSRF
    var csrfToken = '{{ csrf_token() }}';

    var liveTime = document.getElementById('liveTime');
    var todayTotal = document.getElementById('todayTotal');
    var todayMini = document.getElementById('todayMini');
    var currentMini = document.getElementById('currentMini');
    var stateText = document.getElementById('stateText');
    var stateDot = document.getElementById('stateDot');
    var liveSub = document.getElementById('liveSub');

    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function hms(sec) {
        sec = Math.max(0, Math.floor(sec));
        return pad(Math.floor(sec / 3600)) + ':' + pad(Math.floor(sec % 3600 / 60)) + ':' + pad(sec % 60);
    }

    function render() {
        liveTime.textContent = hms(runningSeconds);
        todayTotal.textContent = hms(todaySeconds);
        todayMini.textContent = hms(todaySeconds);
        currentMini.textContent = hms(runningSeconds);
    }

    function refreshState() {
        if (running) {
            stateText.textContent = '正在统计中';
            liveSub.textContent = '停留在本页面，系统会自动累计你的在线摸鱼时长';
            stateDot.classList.remove('paused');
        } else {
            stateText.textContent = '等待心跳开启计时';
            liveSub.textContent = '页面会在 1 分钟内自动开始计时';
            stateDot.classList.add('paused');
        }
    }

    // 秒级本地计数：进行中时本地累加显示，真实累计以心跳/校准返回的后端值为准
    var ticker = setInterval(function () {
        if (running) {
            runningSeconds += 1;
            todaySeconds += 1;
        }
        render();
    }, 1000);

    // 以后端返回的绝对秒数作为基准覆盖本地值（两者对齐，不整页刷新）
    function applySync(runningNow, runningSec, todaySec) {
        running = !!runningNow;
        if (running) {
            runningSeconds = runningSec;
        } else {
            runningSeconds = 0;
        }
        todaySeconds = todaySec;
        refreshState();
        render();
    }

    // 发送心跳（自动开启/刷新会话）
    function sendHeartbeat() {
        return fetch('{{ url('/slack/heartbeat') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: '{}',
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d && d.ok) {
                applySync(d.running, d.running_seconds, d.today_seconds);
            }
        }).catch(function () {
            // 心跳失败不阻塞页面，下个周期会重试
        });
    }

    // 校准状态：只做数据对齐，绝不整页刷新（避免刷新导致的计时中断）
    function pollStatus() {
        return fetch('{{ url('/slack/status') }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (d) {
            applySync(d.running, d.running_seconds, d.today_seconds);
        }).catch(function () {});
    }

    // 立即发送一次心跳开启计时
    sendHeartbeat();

    // 周期性心跳 + 状态校准
    setInterval(sendHeartbeat, {{ $heartbeatInterval }} * 1000);
    setInterval(pollStatus, 30000);

    // 页面可见性变化时，回到前台立刻校准
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) { pollStatus(); }
    });

    refreshState();
    render();
})();
</script>
</body>
</html>
