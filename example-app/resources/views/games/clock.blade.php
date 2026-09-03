<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>错位时钟 - {{ config('app.name', 'Laravel') }}</title>
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
        .sidebar {
            width: 200px;
            background: #fff;
            border-right: 1px solid #ebeef5;
            flex-shrink: 0;
        }
        .sidebar-logo {
            padding: 18px 20px;
            border-bottom: 1px solid #ebeef5;
            font-weight: bold;
            font-size: 16px;
            color: #303133;
        }
        .menu { padding: 8px 0; }
        .menu-item {
            display: block;
            padding: 12px 20px;
            color: #303133;
            cursor: pointer;
            border-left: 3px solid transparent;
        }
        .menu-item:hover { background: #f5f7fa; }
        .menu-item.active {
            background: #ecf5ff;
            color: #409eff;
            border-left-color: #409eff;
        }
        .menu-section {
            padding: 10px 20px 6px;
            color: #909399;
            font-size: 12px;
        }

        /* 右侧主体 */
        .main { flex: 1; min-width: 0; }

        /* 顶部 */
        .topbar {
            background: #fff;
            padding: 10px 20px;
            border-bottom: 1px solid #ebeef5;
            font-size: 13px;
            color: #606266;
        }
        .topbar .sep { color: #c0c4cc; margin: 0 6px; }

        .tab-content { padding: 20px; }

        /* ===== 错位时钟页面 ===== */
        .card {
            background: #fff;
            border: 1px solid #ebeef5;
            border-radius: 8px;
            max-width: 900px;
            margin: 0 auto;
            overflow: hidden;
        }
        .card-head {
            padding: 16px 20px;
            text-align: center;
            background: linear-gradient(135deg, #eef1ff, #e3e8ff);
            border-bottom: 1px solid #d9e0f5;
        }
        .card-head .sub { font-size: 13px; color: #5a68c9; }

        .card-body { padding: 20px; }

        /* 状态栏 */
        .status-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
            padding: 12px 16px;
            background: #fafbfc;
            border: 1px solid #ebeef5;
            border-radius: 8px;
        }
        .status-bar .it { display: flex; flex-direction: column; }
        .status-bar .it .l { font-size: 11px; color: #909399; }
        .status-bar .it .v {
            font-size: 20px; font-weight: 700; color: #303133;
            font-variant-numeric: tabular-nums;
        }
        .status-bar .btn { margin-left: auto; }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            color: #fff;
            background: #409eff;
        }
        .btn:hover { background: #66b1ff; }
        .btn.warn { background: #e6a23c; }
        .btn.warn:hover { background: #ebb563; }
        .btn:disabled { background: #a0cfff; cursor: not-allowed; }

        /* 三根指针区 */
        .dials {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 18px;
        }
        .dial-card {
            background: #fff;
            border: 2px solid #ebeef5;
            border-radius: 12px;
            padding: 14px;
            text-align: center;
            position: relative;
            transition: border-color .2s;
        }
        .dial-card.active { border-color: #409eff; box-shadow: 0 4px 14px rgba(64,158,255,.15); }
        .dial-card.chaos { border-color: #f56c6c; animation: chaosPulse .5s infinite alternate; }
        @keyframes chaosPulse {
            0% { box-shadow: 0 0 0 0 rgba(245,108,108,.4); }
            100% { box-shadow: 0 0 0 10px rgba(245,108,108,0); }
        }

        .dial-card .role {
            font-weight: 600; font-size: 15px; margin-bottom: 2px;
        }
        .dial-card.hour .role { color: #409eff; }
        .dial-card.min .role { color: #67c23a; }
        .dial-card.sec .role { color: #e6a23c; }

        .dial-card .val {
            font-size: 12px; color: #909399; margin-bottom: 8px;
            font-variant-numeric: tabular-nums;
        }

        .dial svg { width: 100%; max-width: 200px; display: block; margin: 0 auto; }
        .dial-card.locked .dial svg { filter: saturate(.6); opacity: .85; }

        /* 指针颜色 */
        .hand-hour { stroke: #409eff; }
        .hand-min  { stroke: #67c23a; }
        .hand-sec  { stroke: #e6a23c; }
        .hand-active { filter: drop-shadow(0 0 6px rgba(64,158,255,.6)); }

        /* 按键行 */
        .keys {
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .key {
            padding: 7px 14px;
            border: 1px solid #dcdfe6;
            background: #fff;
            color: #606266;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }
        .key:hover { color: #409eff; border-color: #c6e2ff; background: #ecf5ff; }
        .key:disabled { color: #c0c4cc; cursor: not-allowed; background: #fafbfc; border-color: #ebeef5; }
        .key small { color: #c0c4cc; margin-left: 4px; font-size: 11px; }

        /* 帮助 */
        .help {
            font-size: 13px; color: #909399; margin-top: 16px; text-align: center;
            line-height: 1.9;
        }
        .help b { color: #606266; }
        .help code {
            background: #f4f4f5; padding: 1px 6px; border-radius: 4px;
            color: #409eff; font-size: 12px;
        }

        /* 结果浮层 */
        .overlay {
            position: fixed; inset: 0; background: rgba(48,49,51,.5);
            display: none; align-items: center; justify-content: center; z-index: 999;
        }
        .overlay.show { display: flex; }
        .overlay .box {
            background: #fff; border-radius: 12px; padding: 28px 36px; text-align: center;
            max-width: 400px; box-shadow: 0 12px 40px rgba(0,0,0,.2);
        }
        .overlay .box .t { font-size: 22px; font-weight: 700; color: #303133; margin-bottom: 6px; }
        .overlay .box .d { color: #606266; font-size: 14px; margin-bottom: 6px; }
        .overlay .box .best { color: #e6a23c; font-size: 13px; margin-bottom: 16px; }
        .overlay .box .row { display: flex; gap: 16px; justify-content: center; margin-bottom: 14px; }
        .overlay .box .row .cell { background: #fafbfc; border: 1px solid #ebeef5; border-radius: 8px; padding: 8px 18px; }
        .overlay .box .row .cell .c1 { font-size: 22px; font-weight: 700; color: #409eff; }
        .overlay .box .row .cell .c2 { font-size: 11px; color: #909399; }

        .toast {
            position: fixed; left: 50%; top: 70px; transform: translateX(-50%);
            background: rgba(245,108,108,.92); color: #fff; padding: 9px 18px;
            border-radius: 20px; font-size: 13px; opacity: 0; pointer-events: none;
            transition: opacity .3s; z-index: 1000; max-width: 80%;
        }
        .toast.show { opacity: 1; }
        .toast.good { background: rgba(103,194,58,.92); }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
            .dials { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'clock'])

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            摸鱼游戏
            <span class="sep">/</span>
            错位时钟
        </div>

        <div class="tab-content">

            <div class="card">
                <div class="card-head">
                    <div style="font-size:15px;font-weight:600;color:#3b4fae;">🕐 错位时钟 · 时间校准挑战</div>
                    <div class="sub">三根指针被"错位"了，快把它们都校准回 12 点</div>
                </div>
                <div class="card-body">

                    <!-- 状态栏 -->
                    <div class="status-bar">
                        <div class="it"><span class="l">点击次数</span><span class="v" id="clicks">0</span></div>
                        <div class="it"><span class="l">耗时(s)</span><span class="v" id="time">0.0</span></div>
                        <div class="it"><span class="l">稳定相位</span><span class="v" id="stable">-</span></div>
                        <button type="button" class="btn warn" id="btn-reset">↺ 重新开局</button>
                    </div>

                    <!-- 三根指针 -->
                    <div class="dials" id="dials">

                        <!-- 时针 -->
                        <div class="dial-card hour" data-idx="0">
                            <div class="role">时针 <small style="font-weight:400;color:#c0c4cc;">(按键 1)</small></div>
                            <div class="val">偏移：<span class="off">--</span>°</div>
                            <div class="dial">
                                <svg viewBox="0 0 200 200">
                                    <!-- 表盘 -->
                                    <circle cx="100" cy="100" r="88" fill="#fbfcff" stroke="#dfe3f0" stroke-width="2"/>
                                    <!-- 刻度 -->
                                    <g stroke="#c0c4cc" stroke-width="1">
                                        @for ($i = 0; $i < 12; $i++)
                                            @php $ang = $i * 30; $cos = cos(deg2rad($ang)); $sin = sin(deg2rad($ang)); @endphp
                                            <line x1="{{ 100 + 74*$cos }}" y1="{{ 100 - 74*$sin }}"
                                                  x2="{{ 100 + 82*$cos }}" y2="{{ 100 - 82*$sin }}"/>
                                        @endfor
                                    </g>
                                    <!-- 12 点高亮刻度 -->
                                    <line x1="100" y1="18" x2="100" y2="34" stroke="#e6a23c" stroke-width="4" stroke-linecap="round"/>
                                    <text x="100" y="30" text-anchor="middle" font-size="10" fill="#e6a23c">▲</text>
                                    <text x="100" y="22" text-anchor="middle" font-size="12" fill="#303133" font-weight="700">12</text>
                                    <!-- 中心 -->
                                    <circle cx="100" cy="100" r="6" fill="#303133"/>
                                    <!-- 指针(默认指向12) -->
                                    <g class="hand hand-hour" data-hand="0">
                                        <line x1="100" y1="100" x2="100" y2="40" stroke="#409eff" stroke-width="8" stroke-linecap="round"/>
                                    </g>
                                </svg>
                            </div>
                        </div>

                        <!-- 分针 -->
                        <div class="dial-card min" data-idx="1">
                            <div class="role">分针 <small style="font-weight:400;color:#c0c4cc;">(按键 2)</small></div>
                            <div class="val">偏移：<span class="off">--</span>°</div>
                            <div class="dial">
                                <svg viewBox="0 0 200 200">
                                    <circle cx="100" cy="100" r="88" fill="#fbfcff" stroke="#dfe3f0" stroke-width="2"/>
                                    <g stroke="#c0c4cc" stroke-width="1">
                                        @for ($i = 0; $i < 12; $i++)
                                            @php $ang = $i * 30; $cos = cos(deg2rad($ang)); $sin = sin(deg2rad($ang)); @endphp
                                            <line x1="{{ 100 + 74*$cos }}" y1="{{ 100 - 74*$sin }}"
                                                  x2="{{ 100 + 82*$cos }}" y2="{{ 100 - 82*$sin }}"/>
                                        @endfor
                                    </g>
                                    <line x1="100" y1="18" x2="100" y2="34" stroke="#e6a23c" stroke-width="4" stroke-linecap="round"/>
                                    <text x="100" y="30" text-anchor="middle" font-size="10" fill="#e6a23c">▲</text>
                                    <text x="100" y="22" text-anchor="middle" font-size="12" fill="#303133" font-weight="700">12</text>
                                    <circle cx="100" cy="100" r="6" fill="#303133"/>
                                    <g class="hand hand-min" data-hand="1">
                                        <line x1="100" y1="100" x2="100" y2="34" stroke="#67c23a" stroke-width="6" stroke-linecap="round"/>
                                    </g>
                                </svg>
                            </div>
                        </div>

                        <!-- 秒针 -->
                        <div class="dial-card sec" data-idx="2">
                            <div class="role">秒针 <small style="font-weight:400;color:#c0c4cc;">(按键 3)</small></div>
                            <div class="val">偏移：<span class="off">--</span>°</div>
                            <div class="dial">
                                <svg viewBox="0 0 200 200">
                                    <circle cx="100" cy="100" r="88" fill="#fbfcff" stroke="#dfe3f0" stroke-width="2"/>
                                    <g stroke="#c0c4cc" stroke-width="1">
                                        @for ($i = 0; $i < 12; $i++)
                                            @php $ang = $i * 30; $cos = cos(deg2rad($ang)); $sin = sin(deg2rad($ang)); @endphp
                                            <line x1="{{ 100 + 74*$cos }}" y1="{{ 100 - 74*$sin }}"
                                                  x2="{{ 100 + 82*$cos }}" y2="{{ 100 - 82*$sin }}"/>
                                        @endfor
                                    </g>
                                    <line x1="100" y1="18" x2="100" y2="34" stroke="#e6a23c" stroke-width="4" stroke-linecap="round"/>
                                    <text x="100" y="30" text-anchor="middle" font-size="10" fill="#e6a23c">▲</text>
                                    <text x="100" y="22" text-anchor="middle" font-size="12" fill="#303133" font-weight="700">12</text>
                                    <circle cx="100" cy="100" r="6" fill="#303133"/>
                                    <g class="hand hand-sec" data-hand="2">
                                        <line x1="100" y1="100" x2="100" y2="26" stroke="#e6a23c" stroke-width="3" stroke-linecap="round"/>
                                    </g>
                                </svg>
                            </div>
                        </div>

                    </div>

                    <!-- 操作按钮 -->
                    <div class="keys" id="keys">
                        <button type="button" class="key" data-act="left">◀ 逆时针 6°<small>Shift微调</small></button>
                        <button type="button" class="key" data-act="right">顺时针 6° ▶<small>Shift微调</small></button>
                    </div>

                    <div class="help">
                        <b>玩法：</b>点击选中一根指针（或按 <code>1 / 2 / 3</code>），再点上方按钮让它在<b>正 / 逆时针</b>方向推进校准。
                        每次校准会触发<b>干扰</b>：另外两根指针会随机 ±乱跳一段，把局面打乱。
                        <br>
                        三根指针都停在 <b>12 点（±2°）</b>并稳定 <b>0.3 秒</b>即通关；
                        若某两根指针夹角过大（≥150°，近乎"背道而驰"）判定<b>过度混乱</b>，本局重置。
                        用尽可能少的点击、最快的速度完成校准吧。
                    </div>

                </div>
            </div>

        </div>

    </main>
</div>

<!-- 结算浮层 -->
<div class="overlay" id="overlay">
    <div class="box">
        <div class="t">🎉 校准完成！</div>
        <div class="d">三根指针已重新指向 12 点，世界恢复秩序。</div>
        <div class="row">
            <div class="cell"><div class="c1" id="res-click">0</div><div class="c2">点击次数</div></div>
            <div class="cell"><div class="c1" id="res-time">0.0s</div><div class="c2">耗时</div></div>
        </div>
        <div class="best" id="res-best"></div>
        <button type="button" class="btn" id="btn-again">再来一局</button>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
(function () {
    var dials = document.querySelectorAll('.dial-card');
    var keyBtns = document.querySelectorAll('#keys .key');
    var toast = document.getElementById('toast');
    var overlay = document.getElementById('overlay');

    // 各指针基准推进与干扰档位(体现"各自速度不同")
    var SPEED = [1.2, 3, 8];        // 时针慢、分针中、秒针快(干扰幅度参考)
    var BIG_STEP = [6, 12, 30];     // 各针每次推进的大角度(°)
    var TOL = 2;                    // 误差 ±2°
    var CHAOS_ANGLE = 120;          // 背道而驰(过度混乱)判定角(°)

    // 状态
    var angle = [0, 0, 0];          // 当前相对12的旋转角(顺时针, 指向哪就是哪)
    var selected = 0;
    var clicks = 0;
    var startedAt = null;
    var timerTextEl = document.getElementById('time');
    var clicksEl = document.getElementById('clicks');
    var stableEl = document.getElementById('stable');
    var btnReset = document.getElementById('btn-reset');
    var btnAgain = document.getElementById('btn-again');
    var offEls = document.querySelectorAll('.off');

    var handGroups = document.querySelectorAll('.hand');

    var stableSince = null;         // 满足对齐的起始时间
    var finished = false;
    var chaosFlash = false;
    var animId = null;

    function normalize(a) { return ((a % 360) + 360) % 360; }

    // 某指针距12点(0°方向)的最小偏移(0~180)
    function offsetTo12(a) {
        var n = normalize(a);
        return Math.min(n, 360 - n);
    }

    function isAligned(a) { return offsetTo12(a) <= TOL; }

    // 夹角 = 两指针指向间的较小角度
    function gapBetween(a, b) {
        var d = Math.abs(normalize(a) - normalize(b));
        return Math.min(d, 360 - d);
    }

    // 检测是否背道而驰(存在某两根夹角过大)
    function isChaotic() {
        for (var i = 0; i < 3; i++) {
            for (var j = i + 1; j < 3; j++) {
                if (gapBetween(angle[i], angle[j]) >= CHAOS_ANGLE) return true;
            }
        }
        return false;
    }

    // 干扰：使 idx 之外、尚未校准(未对齐)的指针发生速度波动偏移。
    // 已经稳稳指向 12 点的指针视为"校准完成"，不再被干扰打乱，从而保证能够通关。
    function disturb(exceptIdx) {
        for (var i = 0; i < 3; i++) {
            if (i === exceptIdx) continue;
            if (isAligned(angle[i])) continue;   // 已校准的不受影响
            // 干扰幅度与该指针"转速"相关(快的更乱), 再叠加随机
            var amp = 6 + SPEED[i] * 5 + Math.random() * 18;
            var dir = Math.random() < 0.5 ? -1 : 1;
            angle[i] += dir * amp;
        }
    }

    function render() {
        // 更新指针旋转
        handGroups.forEach(function (g, i) {
            g.setAttribute('transform', 'rotate(' + angle[i] + ' 100 100)');
        });
        // 更新偏移显示
        offEls.forEach(function (el, i) {
            el.textContent = offsetTo12(angle[i]).toFixed(1);
        });
        // 高亮选中
        dials.forEach(function (d, i) {
            d.classList.toggle('active', i === selected);
        });
        // 干扰红光提示
        dials.forEach(function (d) {
            d.classList.toggle('chaos', chaosFlash && !finished);
        });
    }

    function showToast(msg, good) {
        toast.textContent = msg;
        toast.classList.toggle('good', !!good);
        toast.classList.add('show');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(function () { toast.classList.remove('show'); }, 1800);
    }

    // 开局：随机位置
    function deal() {
        angle[0] = 40 + Math.random() * 320;
        angle[1] = 40 + Math.random() * 320;
        angle[2] = 40 + Math.random() * 320;
        selected = 0;
        clicks = 0;
        startedAt = null;
        finished = false;
        stableSince = null;
        chaosFlash = false;
        clicksEl.textContent = '0';
        timerTextEl.textContent = '0.0';
        stableEl.textContent = '-';
        overlay.classList.remove('show');
        setBtnsEnabled(true);
        render();
    }

    function setBtnsEnabled(on) {
        keyBtns.forEach(function (b) { b.disabled = !on; });
    }

    // 点击推进: dir=+1 顺时针, -1 逆时针, fine=true 时以 1° 微调
    function advance(handIdx, dir, fine) {
        if (finished) return;
        if (!startedAt) startedAt = performance.now();

        var step = fine ? 1 : BIG_STEP[handIdx];
        angle[handIdx] += dir * step;
        clicks++;
        clicksEl.textContent = clicks;

        // 推进动画(高亮该指针一下)
        var g = handGroups[handIdx];
        g.classList.add('hand-active');
        setTimeout(function () { g.classList.remove('hand-active'); }, 120);

        // 干扰其它指针
        disturb(handIdx);
        chaosFlash = true;
        setTimeout(function () { chaosFlash = false; render(); }, 500);

        render();

        // 背道而驰检测 → 重置本局
        if (isChaotic()) {
            showToast('⚠ 过度混乱(两针背道而驰)，本局重置！');
            setTimeout(function () { deal(); showToast('已重新开局，请重新校准'); }, 600);
            return;
        }
    }

    // 选择指针
    function select(idx) {
        selected = idx;
        render();
    }

    // 手柄点击选中
    dials.forEach(function (d, i) {
        d.addEventListener('click', function (e) {
            // 点击表盘区域用于选择该指针
            if (e.target.closest('svg')) select(i);
        });
    });

    keyBtns.forEach(function (b) {
        b.addEventListener('click', function (e) {
            advance(selected, b.dataset.act === 'right' ? 1 : -1, !!e.shiftKey);
        });
    });

    // 键盘: 1/2/3 选针; 左右箭头 推进; Shift+箭头 微调
    document.addEventListener('keydown', function (e) {
        if (e.key === '1') select(0);
        else if (e.key === '2') select(1);
        else if (e.key === '3') select(2);
        else if (e.key === 'ArrowLeft') { advance(selected, -1, !!e.shiftKey); }
        else if (e.key === 'ArrowRight') { advance(selected, 1, !!e.shiftKey); }
    });

    // 实时计时 + 稳定对齐检测
    function loop() {
        if (!finished && startedAt) {
            var el = (performance.now() - startedAt) / 1000;
            timerTextEl.textContent = el.toFixed(1);

            var allAligned = angle.every(isAligned);
            if (allAligned) {
                if (!stableSince) stableSince = performance.now();
                var hold = (performance.now() - stableSince) / 1000;
                stableEl.textContent = hold.toFixed(2) + 's';
                if (hold >= 0.3) {
                    finished = true;
                    setBtnsEnabled(false);
                    stableEl.textContent = '✓ 稳定';
                    setTimeout(finishWin, 150);
                }
            } else {
                stableSince = null;
                stableEl.textContent = '-';
            }
        }
        render();
        animId = requestAnimationFrame(loop);
    }

    // 胜利结算
    function finishWin() {
        var cost = ((performance.now() - startedAt) / 1000).toFixed(1);
        timerTextEl.textContent = cost;

        // 对齐动画: 三针都转到0
        angle = [0, 0, 0];
        render();

        document.getElementById('res-click').textContent = clicks;
        document.getElementById('res-time').textContent = cost + 's';

        // 本地记录挑战(最少点击 & 最快时间)
        var best = JSON.parse(localStorage.getItem('clockBest') || '{"click":99999,"time":99999}');
        var isNewClick = clicks < best.click;
        var isNewTime = parseFloat(cost) < best.time;
        if (isNewClick) best.click = clicks;
        if (isNewTime) best.time = parseFloat(cost);
        localStorage.setItem('clockBest', JSON.stringify(best));

        var parts = [];
        var rec = [];
        rec.push('本机最少点击：' + (best.click === 99999 ? '--' : best.click));
        rec.push('最快通关：' + (best.time === 99999 ? '--' : best.time.toFixed(1) + 's'));
        if (isNewClick) parts.push('🎊 打破最少点击纪录！');
        if (isNewTime) parts.push('🏆 打破最快纪录！');
        parts.push(rec.join(' · '));
        document.getElementById('res-best').textContent = parts.join('\n');

        overlay.classList.add('show');
        showToast('校准完成，功德无量！', true);
    }

    btnReset.addEventListener('click', function () {
        if (!confirm('确定要重置本局重新开始吗？')) return;
        deal();
    });
    btnAgain.addEventListener('click', deal);

    deal();
    loop();
})();
</script>
</body>
</html>
