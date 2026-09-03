<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>敲木鱼 - {{ config('app.name', 'Laravel') }}</title>
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

        /* ===== 敲木鱼页面 ===== */
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
            background: linear-gradient(135deg, #fff8e6, #fff3d6);
            border-bottom: 1px solid #f3e6c8;
        }
        .card-head .sub { font-size: 13px; color: #b08d3f; }

        .card-body { padding: 24px 20px; text-align: center; }

        .merit-box {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 6px;
            margin-bottom: 20px;
        }
        .merit-box .num {
            font-size: 40px;
            font-weight: 700;
            color: #303133;
            font-variant-numeric: tabular-nums;
        }
        .merit-box .unit { font-size: 14px; color: #909399; }

        .merit-label { font-size: 13px; color: #c0c4cc; margin-bottom: 4px; }

        /* 木鱼 */
        .fish-wrap { position: relative; display: inline-block; margin-bottom: 18px; }
        .fish {
            width: 170px;
            height: 170px;
            cursor: pointer;
            user-select: none;
            -webkit-user-select: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 72px;
            background: radial-gradient(circle at 35% 30%, #f8b26a, #d98324);
            box-shadow: 0 10px 22px rgba(217, 131, 36, .35), inset 0 -6px 0 rgba(0,0,0,.08);
            transition: transform .06s ease;
            border: none;
        }
        .fish:active, .fish.hit { transform: scale(.92) translateY(3px); }
        .fish:hover { filter: brightness(1.03); }

        /* 敲击震动 */
        .fish-wrap.shake { animation: shake .18s; }
        @keyframes shake {
            0%, 100% { transform: rotate(0); }
            25% { transform: rotate(-2deg); }
            75% { transform: rotate(2deg); }
        }

        /* 飘字 */
        .float-num {
            position: fixed;
            font-size: 20px;
            font-weight: 700;
            color: #e6a23c;
            pointer-events: none;
            z-index: 99;
            animation: floatUp 1s ease-out forwards;
        }
        @keyframes floatUp {
            0% { opacity: 1; transform: translate(-50%, 0) scale(1); }
            100% { opacity: 0; transform: translate(-50%, -90px) scale(1.4); }
        }

        /* 功德光环 */
        .halo {
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 2px solid rgba(230, 162, 60, .0);
            animation: haloOut .7s ease-out forwards;
            pointer-events: none;
        }
        @keyframes haloOut {
            0% { border-color: rgba(230,162,60,.7); transform: scale(.85); opacity: 1; }
            100% { border-color: rgba(230,162,60,0); transform: scale(1.5); opacity: 0; }
        }

        .subtitle {
            font-size: 13px;
            color: #b0a894;
            margin-top: 4px;
        }

        .btn {
            display: inline-block;
            padding: 8px 20px;
            border: 1px solid #dcdfe6;
            background: #fff;
            color: #606266;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            margin: 0 6px;
        }
        .btn:hover { color: #409eff; border-color: #c6e2ff; }
        .btn.active { background: #ecf5ff; color: #409eff; border-color: #409eff; }

        /* 速度控制栏 */
        .controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .toast {
            position: fixed;
            left: 50%;
            top: 70px;
            transform: translateX(-50%);
            background: rgba(48, 49, 51, .85);
            color: #fff;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s;
            z-index: 999;
        }
        .toast.show { opacity: 1; }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'woodfish'])

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            摸鱼游戏
            <span class="sep">/</span>
            敲木鱼
        </div>

        <div class="tab-content">

            <div class="card">
                <div class="card-head">
                    <div style="font-size:15px;font-weight:600;color:#8c6a1f;">🪵 敲木鱼 · 电子功德</div>
                    <div class="sub">上班累了，敲一敲，佛系摸鱼，功德无量</div>
                </div>
                <div class="card-body">
                    <div class="merit-label">当前功德</div>
                    <div class="merit-box">
                        <span class="num" id="merit-count">0</span>
                        <span class="unit">功德</span>
                    </div>

                    <div class="fish-wrap" id="fish-wrap">
                        <button class="fish" id="fish" title="点击敲木鱼">🪵</button>
                    </div>

                    <div class="subtitle" id="merit-sub">愿今天的烦心事，都随木鱼声消散</div>

                    <div class="controls">
                        <button type="button" class="btn" id="btn-toggle">⏸ 开始自动敲</button>
                        <button type="button" class="btn" id="btn-reset">↺ 重置功德</button>
                    </div>
                </div>
            </div>

        </div>

    </main>
</div>

<div class="toast" id="toast"></div>

<script>
(function () {
    var count = 0;
    var countEl = document.getElementById('merit-count');
    var fish = document.getElementById('fish');
    var wrap = document.getElementById('fish-wrap');
    var btnToggle = document.getElementById('btn-toggle');
    var btnReset = document.getElementById('btn-reset');
    var subtitle = document.getElementById('merit-sub');
    var toast = document.getElementById('toast');

    var phrases = [
        '愿今天的烦心事，都随木鱼声消散',
        '功德 +1，摸鱼有理',
        '打工人的精神寄托',
        '领导看不见我，我只看见佛',
        '烦恼退散，功德圆满',
        '敲一敲，心静自然凉'
    ];

    var autoTimer = null;
    var autoOn = false;

    function showToast(msg) {
        toast.textContent = msg;
        toast.classList.add('show');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(function () {
            toast.classList.remove('show');
        }, 1600);
    }

    function bump() {
        count++;
        countEl.textContent = count;

        // 震动画
        fish.classList.remove('hit');
        void fish.offsetWidth; // 触发重排以便重新播放动画
        fish.classList.add('hit');

        // 光环
        var halo = document.createElement('div');
        halo.className = 'halo';
        wrap.appendChild(halo);
        setTimeout(function () { halo.remove(); }, 700);

        // 随机提示语
        if (count % 10 === 0) {
            subtitle.textContent = phrases[Math.floor(Math.random() * phrases.length)];
        }
    }

    // 点击敲击，产生随机的 1~3 功德
    fish.addEventListener('click', function () {
        var step = 1 + Math.floor(Math.random() * 3);
        for (var i = 0; i < step; i++) bump();
        showToast('功德 +' + step);
    });

    // 自动敲击（摸鱼神器，老板过来一键暂停）
    function startAuto() {
        if (autoTimer) return;
        autoTimer = setInterval(function () {
            bump();
            countEl.textContent = count;
        }, 700);
    }
    function stopAuto() {
        clearInterval(autoTimer);
        autoTimer = null;
    }

    btnToggle.addEventListener('click', function () {
        autoOn = !autoOn;
        if (autoOn) {
            startAuto();
            btnToggle.textContent = '⏸ 暂停自动敲';
            showToast('自动摸鱼模式已开启 🐟');
        } else {
            stopAuto();
            btnToggle.textContent = '▶ 继续自动敲';
            showToast('已暂停，老板来了快收好');
        }
    });

    btnReset.addEventListener('click', function () {
        if (count > 0 && !confirm('确定要清空功德吗？' + (autoOn ? '（自动敲击会继续）' : ''))) {
            return;
        }
        count = 0;
        countEl.textContent = '0';
        subtitle.textContent = '愿今天的烦心事，都随木鱼声消散';
        showToast('功德已清空，从零开始积德');
    });
})();
</script>
</body>
</html>
