<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>打地鼠 - {{ config('app.name', 'Laravel') }}</title>
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

        /* ===== 打地鼠页面 ===== */
        .card {
            background: #fff;
            border: 1px solid #ebeef5;
            border-radius: 8px;
            max-width: 620px;
            margin: 0 auto;
            overflow: hidden;
        }
        .card-head {
            padding: 16px 20px;
            text-align: center;
            background: linear-gradient(135deg, #eefbe9, #dcf7d3);
            border-bottom: 1px solid #d5eccd;
        }
        .card-head .sub { font-size: 13px; color: #5b9a44; }

        .card-body { padding: 20px; }

        /* 计分栏 */
        .score-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
            padding: 12px 16px;
            background: #fafbfc;
            border: 1px solid #ebeef5;
            border-radius: 8px;
        }
        .score-bar .item { display: flex; align-items: baseline; gap: 6px; }
        .score-bar .item .v {
            font-size: 22px;
            font-weight: 700;
            color: #303133;
            font-variant-numeric: tabular-nums;
        }
        .score-bar .item .l { font-size: 12px; color: #909399; }
        .score-bar .timers { display: flex; align-items: center; gap: 14px; }

        /* 按钮 */
        .btn {
            padding: 8px 22px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            color: #fff;
            background: #67c23a;
        }
        .btn:hover { background: #85ce61; }
        .btn:disabled { background: #a0cfff; cursor: not-allowed; }
        .btn.warn { background: #e6a23c; }
        .btn.warn:hover { background: #ebb563; }

        /* 游戏场地 */
        .field {
            position: relative;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            padding: 20px;
            background: linear-gradient(180deg, #bfe6a3, #a6d98a);
            border-radius: 10px;
            min-height: 300px;
        }
        .hole {
            position: relative;
            height: 88px;
            background: radial-gradient(ellipse at 50% 70%, #6b4f2a, #4c381e);
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            box-shadow: inset 0 6px 10px rgba(0,0,0,.25);
        }
        .hole .mole {
            position: absolute;
            left: 50%;
            bottom: 8px;
            transform: translateX(-50%) translateY(120%);
            width: 52px;
            height: 52px;
            font-size: 34px;
            line-height: 52px;
            text-align: center;
            transition: transform .12s ease-out;
            background: radial-gradient(circle at 35% 30%, #d9a45f, #a9712f);
            border-radius: 50% 50% 44% 44%;
            box-shadow: 0 4px 8px rgba(0,0,0,.25);
        }
        .hole.up .mole { transform: translateX(-50%) translateY(0); }
        .hole.bonus .mole { background: radial-gradient(circle at 35% 30%, #ffd76a, #f0a93a); }
        .hole:active { cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"><circle cx="14" cy="14" r="12" fill="%23e6a23c" opacity="0.4"/></svg>') 14 14, auto; }

        /* 锤子命中动画由 JS 控制，这里做击打后的闪烁 */
        .hole.hit {
            animation: hitFlash .15s;
        }
        @keyframes hitFlash {
            0% { box-shadow: inset 0 6px 10px rgba(0,0,0,.25), 0 0 0 4px rgba(255,255,255,.9); }
            100% { box-shadow: inset 0 6px 10px rgba(0,0,0,.25); }
        }

        .field.locked .hole { cursor: default; }

        /* 飘字 */
        .float-num {
            position: absolute;
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            text-shadow: 0 1px 3px rgba(0,0,0,.3);
            pointer-events: none;
            z-index: 5;
            animation: floatUp .8s ease-out forwards;
        }
        .float-num.good { color: #fff; }
        .float-num.bad { color: #f56c6c; }
        @keyframes floatUp {
            0% { opacity: 1; transform: translate(-50%, 0); }
            100% { opacity: 0; transform: translate(-50%, -60px); }
        }

        /* 游戏结束遮罩 */
        .overlay {
            position: absolute;
            inset: 0;
            background: rgba(48, 49, 51, .55);
            border-radius: 10px;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            color: #fff;
            z-index: 10;
        }
        .overlay.show { display: flex; }
        .overlay .result { font-size: 26px; font-weight: 700; }
        .overlay .tip { font-size: 14px; color: rgba(255,255,255,.85); }

        /* 说明 */
        .help {
            font-size: 13px;
            color: #909399;
            margin-top: 14px;
            text-align: center;
            line-height: 1.7;
        }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
            .field { min-height: 220px; }
            .hole { height: 70px; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'whack'])

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            摸鱼游戏
            <span class="sep">/</span>
            打地鼠
        </div>

        <div class="tab-content">

            <div class="card">
                <div class="card-head">
                    <div style="font-size:15px;font-weight:600;color:#4c6a2a;">🔨 打地鼠 · 解压摸鱼</div>
                    <div class="sub">把烦人的工作当土拨鼠，见一只拍一只</div>
                </div>
                <div class="card-body">

                    <!-- 计分栏 -->
                    <div class="score-bar">
                        <div class="item">
                            <span class="v" id="score">0</span>
                            <span class="l">得分</span>
                        </div>
                        <div class="item">
                            <span class="v" id="combo">0</span>
                            <span class="l">连击</span>
                        </div>
                        <div class="timers">
                            <div class="item">
                                <span class="v" id="time">0</span>
                                <span class="l">剩余(s)</span>
                            </div>
                            <button type="button" class="btn" id="btn-start">▶ 开始游戏</button>
                        </div>
                    </div>

                    <!-- 场地 -->
                    <div class="field" id="field">
                        <div class="hole" data-idx="0"><span class="mole">🐭</span></div>
                        <div class="hole" data-idx="1"><span class="mole">🐭</span></div>
                        <div class="hole" data-idx="2"><span class="mole">🐭</span></div>
                        <div class="hole" data-idx="3"><span class="mole">🐭</span></div>
                        <div class="hole" data-idx="4"><span class="mole">🐭</span></div>
                        <div class="hole" data-idx="5"><span class="mole">🐭</span></div>
                        <div class="hole" data-idx="6"><span class="mole">🐭</span></div>
                        <div class="hole" data-idx="7"><span class="mole">🐭</span></div>
                        <div class="hole" data-idx="8"><span class="mole">🐭</span></div>

                        <div class="overlay" id="overlay">
                            <div class="result" id="overlay-result">游戏结束</div>
                            <div class="tip" id="overlay-tip">得分：0，点击下方按钮再来一局</div>
                            <button type="button" class="btn warn" id="btn-again">↺ 再来一局</button>
                        </div>
                    </div>

                    <div class="help">
                        💡 点击冒头的地鼠得分，普通地鼠 <b>+1</b>，金色地鼠 <b>+3</b>（出现时间更短）。<br>
                        连续命中累积连击，连击越多单次得分越高，30 秒内看看你能敲走多少"烦心事"。
                    </div>

                </div>
            </div>

        </div>

    </main>
</div>

<script>
(function () {
    var holes = Array.prototype.slice.call(document.querySelectorAll('.hole'));
    var scoreEl = document.getElementById('score');
    var comboEl = document.getElementById('combo');
    var timeEl = document.getElementById('time');
    var btnStart = document.getElementById('btn-start');
    var btnAgain = document.getElementById('btn-again');
    var overlay = document.getElementById('overlay');
    var overlayResult = document.getElementById('overlay-result');
    var overlayTip = document.getElementById('overlay-tip');
    var field = document.getElementById('field');

    var TOTAL_TIME = 30;
    var score = 0;
    var combo = 0;
    var bestCombo = 0;
    var remaining = TOTAL_TIME;
    var playing = false;

    var timers = [];
    var clock = null;

    function clearGameTimers() {
        timers.forEach(clearTimeout);
        timers = [];
        clearInterval(clock);
        clock = null;
    }

    function resetBoard() {
        holes.forEach(function (h) {
            h.classList.remove('up', 'bonus', 'hit');
        });
    }

    function endGame() {
        playing = false;
        clearGameTimers();
        resetBoard();
        btnStart.textContent = '▶ 开始游戏';
        btnStart.disabled = false;
        field.classList.remove('locked');
        overlayResult.textContent = '得分：' + score;
        overlayTip.textContent = (score >= 30 ? '好身手！地鼠都被你敲怕了' :
                                 score >= 15 ? '不错，摸鱼也是生产力！' :
                                 '再练练，下次让土拨鼠排队挨打') + '，最大连击 ' + bestCombo;
        overlay.classList.add('show');
    }

    function startGame() {
        // 初始化
        score = 0;
        combo = 0;
        bestCombo = 0;
        remaining = TOTAL_TIME;
        scoreEl.textContent = '0';
        comboEl.textContent = '0';
        timeEl.textContent = String(TOTAL_TIME);
        overlay.classList.remove('show');
        resetBoard();
        clearGameTimers();

        playing = true;
        btnStart.textContent = '游戏中...';
        btnStart.disabled = true;
        field.classList.remove('locked');

        // 倒计时
        clock = setInterval(function () {
            remaining--;
            timeEl.textContent = remaining;
            if (remaining <= 0) {
                endGame();
            }
        }, 1000);

        // 主循环：随机让地鼠冒头
        (function spawnLoop() {
            if (!playing) return;
            var delay = 420 + Math.random() * 500;
            timers.push(setTimeout(function () {
                if (!playing) return;
                spawnOne();
                spawnLoop();
            }, delay));
        })();
    }

    function spawnOne() {
        var avail = holes.filter(function (h) {
            return !h.classList.contains('up');
        });
        if (!avail.length) return;

        var hole = avail[Math.floor(Math.random() * avail.length)];
        var isBonus = Math.random() < 0.18;
        hole.classList.add('up');
        if (isBonus) hole.classList.add('bonus');

        // 地鼠存活时长（金色更短）
        var life = (isBonus ? 650 : 950) + Math.random() * 400;
        timers.push(setTimeout(function () {
            hole.classList.remove('up', 'bonus');
        }, life));
    }

    function floatText(hole, text, good) {
        var box = hole.getBoundingClientRect();
        var fieldBox = field.getBoundingClientRect();
        var el = document.createElement('div');
        el.className = 'float-num ' + (good ? 'good' : 'bad');
        el.textContent = text;
        // 相对 field 定位
        field.style.position = 'relative';
        el.style.left = (hole.offsetLeft + hole.offsetWidth / 2) + 'px';
        el.style.top = (hole.offsetTop + 10) + 'px';
        field.appendChild(el);
        setTimeout(function () { el.remove(); }, 800);
    }

    // 点击击打
    holes.forEach(function (hole) {
        hole.addEventListener('click', function (e) {
            if (!playing) {
                if (!hole.classList.contains('up')) {
                    // 未开始点击给个提示
                    return;
                }
            }
            if (!hole.classList.contains('up')) {
                return;
            }

            // 命中特效
            hole.classList.remove('hit');
            void hole.offsetWidth;
            hole.classList.add('hit');

            var isBonus = hole.classList.contains('bonus');
            var base = isBonus ? 3 : 1;
            combo++;
            bestCombo = Math.max(bestCombo, combo);
            // 连击加成：最多额外 +3
            var bonus = Math.min(Math.floor(combo / 5), 3);
            var gained = base + bonus;

            score += gained;
            scoreEl.textContent = score;
            comboEl.textContent = combo;

            floatText(hole, '+' + gained, true);

            // 地鼠被打掉
            hole.classList.remove('up', 'bonus');
        });
    });

    // 未命中也会消耗连击（用于冷却）
    var missCooldown = null;
    holes.forEach(function (hole) {
        hole.addEventListener('click', function () {
            // 已经在上面处理命中，这里只处理未命中时的连击清零
            if (!playing) return;
            if (!hole.classList.contains('up')) {
                if (combo > 0 && !missCooldown) {
                    combo = 0;
                    comboEl.textContent = '0';
                    missCooldown = true;
                    setTimeout(function () { missCooldown = false; }, 300);
                }
            }
        });
    });

    btnStart.addEventListener('click', function () {
        if (!playing) startGame();
    });
    btnAgain.addEventListener('click', function () {
        startGame();
    });
})();
</script>
</body>
</html>
