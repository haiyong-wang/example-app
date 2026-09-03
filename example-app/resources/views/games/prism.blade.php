<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>光影迷阵 - {{ config('app.name', 'Laravel') }}</title>
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

        /* ===== 光影迷阵页面 ===== */
        .card {
            background: #fff;
            border: 1px solid #ebeef5;
            border-radius: 8px;
            max-width: 1100px;
            margin: 0 auto;
            overflow: hidden;
        }
        .card-head {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: #eaeaf0;
            border-bottom: 1px solid #2a2a4a;
        }
        .card-head .lt { display: flex; flex-direction: column; }
        .card-head .ttl { font-size: 15px; font-weight: 600; color: #ffd76a; }
        .card-head .sub { font-size: 12px; color: #8f9bb3; }

        /* 顶栏实时状态 */
        .hud {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .hud .cell { text-align: center; background: rgba(255,255,255,.06); border-radius: 6px; padding: 4px 10px; min-width: 56px; }
        .hud .cell .cv { font-size: 18px; font-weight: 700; color: #ffd76a; font-variant-numeric: tabular-nums; }
        .hud .cell .cl { font-size: 10px; color: #8f9bb3; }

        /* 控制条 */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            padding: 12px 20px;
            background: #fafbfc;
            border-bottom: 1px solid #ebeef5;
        }
        .toolbar .btn {
            padding: 7px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            color: #fff;
            background: #409eff;
        }
        .toolbar .btn:hover { background: #66b1ff; }
        .toolbar .btn.ghost { background: #fff; color: #606266; border: 1px solid #dcdfe6; }
        .toolbar .btn.ghost:hover { color: #409eff; border-color: #c6e2ff; }
        .toolbar .btn.warn { background: #e6a23c; }
        .toolbar .btn.warn:hover { background: #ebb563; }

        .toolbar .prism-sel {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .toolbar .ps {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border: 2px solid #dcdfe6;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            background: #fff;
            color: #606266;
            transition: all .15s;
            user-select: none;
        }
        .toolbar .ps.active { border-color: #409eff; color: #409eff; background: #ecf5ff; }
        .toolbar .ps .dot { width: 12px; height: 12px; border-radius: 3px; }
        .toolbar .hint { font-size: 12px; color: #c0c4cc; }
        .toolbar .hint b { color: #909399; }

        /* 游戏舞台 */
        .stage-wrap {
            position: relative;
            padding: 16px;
            background: radial-gradient(circle at 50% 40%, #141430, #06060f);
        }
        #game {
            display: block;
            width: 100%;
            background: transparent;
            border-radius: 6px;
            box-shadow: 0 0 40px rgba(120,120,255,.12) inset;
        }

        /* 覆盖层(开始/结束/过场) */
        .stage-overlay {
            position: absolute;
            inset: 16px;
            background: rgba(6,6,15,.72);
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 14px;
            color: #eaeaf0;
            text-align: center;
            border-radius: 6px;
            z-index: 5;
        }
        .stage-overlay.show { display: flex; }
        .stage-overlay .big { font-size: 26px; font-weight: 800; color: #ffd76a; }
        .stage-overlay .mid { font-size: 18px; font-weight: 700; color: #fff; }
        .stage-overlay .desc { font-size: 13px; color: #aab; max-width: 520px; line-height: 1.8; }
        .stage-overlay .desc b { color: #ffd76a; }
        .stage-overlay .stat { font-size: 14px; color: #c3ccdd; }

        /* 帮助 */
        .help {
            font-size: 13px;
            color: #909399;
            padding: 12px 20px;
            line-height: 2;
        }
        .help b { color: #606266; }
        .help code {
            background: #f4f4f5; padding: 1px 6px; border-radius: 4px;
            color: #409eff; font-size: 12px;
        }
        .legend { display: inline-flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .legend span { display: inline-flex; align-items: center; gap: 5px; }
        .legend .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
            .card-head { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'prism'])

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            摸鱼游戏
            <span class="sep">/</span>
            光影迷阵
        </div>

        <div class="tab-content">

            <div class="card">

                <div class="card-head">
                    <div class="lt">
                        <span class="ttl">✨ 光影迷阵 · 光路求生</span>
                        <span class="sub">用有限的棱镜搭一条"安全光路"，在激光中活下去</span>
                    </div>
                    <div class="hud">
                        <div class="cell"><div class="cv" id="h-level">1</div><div class="cl">关卡</div></div>
                        <div class="cell"><div class="cv" id="h-time">60</div><div class="cl">剩余(s)</div></div>
                        <div class="cell"><div class="cv" id="h-lives">♥ 3</div><div class="cl">生命</div></div>
                        <div class="cell"><div class="cv" id="h-prism">5</div><div class="cl">棱镜</div></div>
                        <div class="cell"><div class="cv" id="h-score">0</div><div class="cl">碎片</div></div>
                    </div>
                </div>

                <!-- 控制条 -->
                <div class="toolbar">
                    <button type="button" class="btn" id="btn-start">▶ 开始游戏</button>
                    <div class="prism-sel">
                        <span class="ps active" data-t="0" title="激光 90° 反射">
                            <span class="dot" style="background:#409eff;"></span>反射
                        </span>
                        <span class="ps" data-t="1" title="激光减速并偏转 45°">
                            <span class="dot" style="background:#67c23a;"></span>折射
                        </span>
                        <span class="ps" data-t="2" title="一分为二，增加弹幕">
                            <span class="dot" style="background:#e6a23c;"></span>分裂
                        </span>
                    </div>
                    <span class="hint">滚轮 或 <b>Q/E</b> 切换棱镜 · 点击舞台空白处放置 · <b>R</b> 移除最后一个棱镜</span>
                    <button type="button" class="btn ghost" id="btn-guide" style="margin-left:auto;">玩法说明</button>
                </div>

                <!-- 舞台 -->
                <div class="stage-wrap">
                    <canvas id="game" width="1000" height="560"></canvas>

                    <!-- 开始/结束覆盖层 -->
                    <div class="stage-overlay show" id="overlay">
                        <div class="big" id="ov-title">✨ 光影迷阵</div>
                        <div class="desc" id="ov-desc">
                            你是一只被困在黑暗空间的小光点。用鼠标移动自己，点击放置 <b>棱镜</b> 改变激光方向，
                            收集 <b>金色碎片</b>，在越来越密集的 <b>彩色激光</b> 中撑过 <b>60 秒</b> 通关这一关。
                        </div>
                        <button type="button" class="btn warn" id="ov-btn">开始游戏</button>
                    </div>
                </div>

                <!-- 帮助 -->
                <div class="help" id="helpbox">
                    <b>操作：</b>鼠标移动控制光点 ·
                    <code>点击</code>空白处放置棱镜 ·
                    <code>滚轮/Q/E</code>切换棱镜类型 ·
                    <code>R</code>移除最近一个棱镜
                    <br>
                    <span class="legend">
                        <span><span class="dot" style="background:#e64a4a;"></span>红激光(最快)</span>
                        <span><span class="dot" style="background:#409eff;"></span>蓝激光</span>
                        <span><span class="dot" style="background:#5bd75b;"></span>绿激光(最慢)</span>
                        <span><span class="dot" style="background:#ffd76a;"></span>光能碎片</span>
                        <span><span class="dot" style="background:#fff;box-shadow:0 0 6px #fff;"></span>你</span>
                    </span>
                    <b style="margin-left:14px;">棱镜：</b>
                    反射=90°反弹 · 折射=减速半速+偏转45° · 分裂=一分为二(弹幕暴增)
                    <br>
                    存活越久激光越密；每过一关激光会更快更多。祝你好运 🐟
                </div>

            </div>

        </div>

    </main>
</div>

<div class="toast" style="position:fixed;left:50%;top:70px;transform:translateX(-50%);background:rgba(48,49,51,.85);color:#fff;padding:8px 18px;border-radius:20px;font-size:13px;opacity:0;pointer-events:none;transition:opacity .3s;z-index:999;" id="toast"></div>

<script>
(function () {
    // ============ 基础引用 ============
    var canvas = document.getElementById('game');
    var ctx = canvas.getContext('2d');
    var W = canvas.width, H = canvas.height;

    var overlay = document.getElementById('overlay');
    var ovTitle = document.getElementById('ov-title');
    var ovDesc = document.getElementById('ov-desc');
    var ovBtn = document.getElementById('ov-btn');
    var btnStart = document.getElementById('btn-start');
    var btnGuide = document.getElementById('btn-guide');
    var helpbox = document.getElementById('helpbox');
    var toast = document.getElementById('toast');

    var hLevel = document.getElementById('h-level');
    var hTime = document.getElementById('h-time');
    var hLives = document.getElementById('h-lives');
    var hPrism = document.getElementById('h-prism');
    var hScore = document.getElementById('h-score');
    var prismBtns = document.querySelectorAll('.ps');

    // ============ 配置 ============
    var COLORS = { red: { c: '#e64a4a', glow: 'rgba(230,74,74,.35)' }, blue: { c: '#4a8fe6', glow: 'rgba(74,143,230,.35)' }, green: { c: '#5bd75b', glow: 'rgba(91,215,91,.35)' } };
    var SPEED_BASE = { red: 320, blue: 220, green: 140 };  // px/s (随关卡放大)
    var REFLECT_MARGIN = 10;    // 边界留白(避开玩家出生中心附近边界其实无妨)
    var PRISM_INIT = 5;
    var PRISM_MAX = 10;
    var LEVEL_SECONDS = 60;
    var FRAG_GROW = 3;           // 每存活多少秒补 1 棱镜

    // ============ 运行时状态 ============
    var player = { x: W / 2, y: H / 2, r: 7, speedBoost: 0, boostT: 0 };
    var prisms = [];             // {x,y,type,total 分多少}
    var laser = [];              // {x,y,vx,vy,color,split}
    var fragments = [];          // {x,y,r,type:'frag'|'boost'}
    var level = 1;
    var elapsed = 0;             // 当前关卡已存活时间(s)
    var levelLeft = LEVEL_SECONDS;
    var score = 0;               // 收集碎片数
    var lives = 3;
    var prismCount = PRISM_INIT;
    var selType = 0;             // 0反射 1折射 2分裂
    var running = false;
    var won = false;
    var overReason = '';
    var fragAccum = 0;

    var spawnTimers = { red: 0, blue: 0, green: 0 };
    var lastT = 0;
    var raf = null;

    // 出生在窗口内一个安全位置(不贴边便于逃生)
    function randomInside() {
        return { x: 100 + Math.random() * (W - 200), y: 80 + Math.random() * (H - 160) };
    }

    function showToast(msg) {
        toast.textContent = msg;
        toast.style.opacity = '1';
        clearTimeout(showToast._t);
        showToast._t = setTimeout(function () { toast.style.opacity = '0'; }, 1800);
    }

    function speedOf(color, lv) {
        return SPEED_BASE[color] * (1 + (lv - 1) * 0.25);
    }

    // 从某条边界内随机一点沿水平/垂直朝场内发射
    function fireFromSide() {
        var colors = ['red', 'blue', 'green'];
        var color = colors[Math.floor(Math.random() * colors.length)];
        var side = Math.floor(Math.random() * 4);
        var x, y, vx, vy;
        var s = speedOf(color, level);
        var m = 40; // 边界内侧起点偏移
        if (side === 0) { x = m + Math.random() * (W - 2 * m); y = 0; vy = 1; vx = 0; }
        else if (side === 1) { x = m + Math.random() * (W - 2 * m); y = H; vy = -1; vx = 0; }
        else if (side === 2) { x = 0; y = m + Math.random() * (H - 2 * m); vx = 1; vy = 0; }
        else { x = W; y = m + Math.random() * (H - 2 * m); vx = -1; vy = 0; }
        laser.push({ x: x, y: y, vx: vx * s, vy: vy * s, color: color, dead: false });
    }

    function fireBurst(n) {
        for (var i = 0; i < n; i++) fireFromSide();
    }

    function spawnFragment() {
        var type = Math.random() < 0.7 ? 'frag' : 'boost';
        var p = randomInside();
        fragments.push({ x: p.x, y: p.y, r: type === 'frag' ? 7 : 9, type: type });
    }

    function resetAll() {
        prisms = []; laser = []; fragments = [];
        player.x = W / 2; player.y = H / 2;
        player.speedBoost = 0; player.boostT = 0;
        score = 0; lives = 3; prismCount = PRISM_INIT;
        elapsed = 0; levelLeft = LEVEL_SECONDS; fragAccum = 0;
        spawnTimers = { red: 0, blue: 0, green: 0 };
        selType = 0;
        won = false;
        syncPrismUI();
        renderHud();
    }

    function startGame(fromLevel1) {
        if (fromLevel1) { level = 1; resetAll(); }
        running = true;
        won = false;
        overlay.classList.remove('show');
        // 初始刷几束与一个碎片
        fireBurst(2);
        spawnFragment();
        lastT = performance.now();
        cancelAnimationFrame(raf);
        loop();
    }

    // ============ 主循环 ============
    function loop() {
        if (!running) return;
        var now = performance.now();
        var dt = Math.min((now - lastT) / 1000, 0.05);
        lastT = now;

        update(dt);
        draw();

        // HUD 每秒刷(节流到每帧即可，直接更新)
        renderHud();

        // 胜利判定
        if (levelLeft <= 0 && running && !won) {
            finishLevel();
            return;
        }

        raf = requestAnimationFrame(loop);
    }

    function update(dt) {
        elapsed += dt;
        levelLeft = LEVEL_SECONDS - elapsed;
        if (levelLeft < 0) levelLeft = 0;

        // 恢复棱镜(随时间缓慢)
        fragAccum += dt;
        if (fragAccum >= FRAG_GROW && prismCount < PRISM_MAX) {
            prismCount++;
            fragAccum = 0;
            showToast('棱镜 +1（随时间恢复）');
            syncPrismUI();
        }

        // 移动玩家(鼠标)由 mousemove 直接设置，这里仅处理加速buff消退
        if (player.boostT > 0) {
            player.boostT -= dt;
            if (player.boostT <= 0) player.speedBoost = 0;
        }

        // 生成激光 —— 随存活时间/关卡越来越密集
        // 关卡 1：约每 2.2s 一束；之后加快，且随机颜色速度不同已由 fireFromSide 保证
        var gap = Math.max(0.7, 2.2 - elapsed * 0.02 - (level - 1) * 0.5);
        spawnTimers.red -= dt;
        if (spawnTimers.red <= 0) {
            // 红/蓝/绿轮流为主色可造成更丰富的组合，这里随机 1~2 束
            var burst = 1 + (Math.random() < (level >= 2 ? 0.4 : 0.15) ? 1 : 0);
            for (var b = 0; b < burst; b++) fireFromSide();
            spawnTimers.red = gap * (0.8 + Math.random() * 0.5);
        }

        // 更新激光(反射、棱镜、移动)
        updateLaser(dt);

        // 更新碎片
        for (var i = fragments.length - 1; i >= 0; i--) {
            var f = fragments[i];
            if (f.t > 0) { f.t -= dt; }
            var dx = f.x - player.x, dy = f.y - player.y;
            var d = Math.sqrt(dx * dx + dy * dy);
            if (d < player.r + f.r + 4) {
                // 收集
                if (f.type === 'boost') {
                    player.speedBoost = 1; player.boostT = 5;
                    showToast('⚡ 移速提升 5 秒！');
                } else {
                    score++;
                    if (prismCount < PRISM_MAX) { prismCount++; showToast('棱镜 +1'); syncPrismUI(); }
                    else showToast('碎片 +1');
                }
                fragments.splice(i, 1);
                if (fragments.length < 2 && Math.random() < 0.5) spawnFragment();
            }
        }
        if (fragments.length === 0) spawnFragment();
    }

    function updateLaser(dt) {
        // 所有棱镜的交互冷却递减
        for (var p = 0; p < prisms.length; p++) {
            if (prisms[p].cool > 0) prisms[p].cool -= dt;
        }

        for (var i = laser.length - 1; i >= 0; i--) {
            var l = laser[i];
            if (l.dead) { laser.splice(i, 1); continue; }

            // 分步移动避免穿透棱镜/玩家
            var steps = 5;
            var perx = l.vx * dt / steps, pery = l.vy * dt / steps;
            for (var s = 0; s < steps; s++) {
                var nx = l.x + perx, ny = l.y + pery;

                // 边界反射
                if (nx < 0) { l.vx = Math.abs(l.vx); nx = 0; }
                if (nx > W) { l.vx = -Math.abs(l.vx); nx = W; }
                if (ny < 0) { l.vy = Math.abs(l.vy); ny = 0; }
                if (ny > H) { l.vy = -Math.abs(l.vy); ny = H; }
                l.x = nx; l.y = ny;

                // 撞玩家
                var dx = l.x - player.x, dy = l.y - player.y;
                if (dx * dx + dy * dy < (player.r + 3) * (player.r + 3)) {
                    hitPlayer(l);
                    l.dead = true;
                    break;
                }

                // 撞棱镜(仅当该棱镜不在冷却中)
                for (var q = 0; q < prisms.length; q++) {
                    var pr = prisms[q];
                    if (pr.cool > 0) continue;
                    var pdx = l.x - pr.x, pdy = l.y - pr.y;
                    if (pdx * pdx + pdy * pdy < (pr.r + 3) * (pr.r + 3)) {
                        interactPrism(l, pr);
                        pr.cool = 0.12;     // 该棱镜短冷却，避免同一束连续多步反复触发
                        pr.hitT = 1;         // 闪光
                        break;
                    }
                }
                if (l.dead) break;
            }
        }
    }

    // 玩家被击中
    function hitPlayer(l) {
        lives--;
        renderHud();
        if (lives <= 0) {
            endGame(false);
        } else {
            showToast('💥 被激光击中，剩余生命 ' + lives);
            player.boostT = Math.max(player.boostT, 0.6); // 轻微缓冲
        }
    }

    // 棱镜交互。根据类型改变激光方向。触发后把激光推出棱镜范围。
    function interactPrism(l, pr) {
        var speed = Math.hypot(l.vx, l.vy);
        var ang = Math.atan2(l.vy, l.vx);
        var pushDist = pr.r + 8;

        if (pr.type === 0) {
            // 反射棱镜：90° 反射(交替转向，避免在单棱镜处反复打转)
            pr.turn = (pr.turn || 1) * -1;
            var na = ang + (Math.PI / 2) * pr.turn;
            l.vx = Math.cos(na) * speed;
            l.vy = Math.sin(na) * speed;
        } else if (pr.type === 1) {
            // 折射棱镜：速度减半 + 偏转 45°
            var dir2 = Math.random() < 0.5 ? 1 : -1;
            var na2 = ang + (Math.PI / 4) * dir2;
            var newSp = speed * 0.5;
            l.vx = Math.cos(na2) * newSp;
            l.vy = Math.sin(na2) * newSp;
        } else if (pr.type === 2) {
            // 分裂棱镜：一分为二(激光过多时不再分裂，防止爆炸卡顿)
            if (laser.length < 320) {
                var spread = Math.PI / 6;
                laser.push({
                    x: l.x, y: l.y,
                    vx: Math.cos(ang - spread) * speed,
                    vy: Math.sin(ang - spread) * speed,
                    color: l.color, dead: false
                });
            }
            l.vx = Math.cos(ang + spread) * speed;
            l.vy = Math.sin(ang + spread) * speed;
        }

        // 把激光推出棱镜范围，避免折射减速后原地纠缠
        var out = speed > 0 ? speed : 1;
        l.x += (l.vx / out) * pushDist;
        l.y += (l.vy / out) * pushDist;
    }

    // ============ 渲染 ============
    function draw() {
        ctx.clearRect(0, 0, W, H);
        // 暗色网格
        ctx.fillStyle = '#0a0a18';
        ctx.fillRect(0, 0, W, H);
        ctx.strokeStyle = 'rgba(80,80,140,.08)';
        ctx.lineWidth = 1;
        for (var gx = 0; gx < W; gx += 40) { ctx.beginPath(); ctx.moveTo(gx, 0); ctx.lineTo(gx, H); ctx.stroke(); }
        for (var gy = 0; gy < H; gy += 40) { ctx.beginPath(); ctx.moveTo(0, gy); ctx.lineTo(W, gy); ctx.stroke(); }

        // 边框光
        ctx.strokeStyle = 'rgba(120,120,255,.25)';
        ctx.strokeRect(1, 1, W - 2, H - 2);

        // 激光
        laser.forEach(function (l) {
            var cc = COLORS[l.color].c;
            ctx.save();
            ctx.globalCompositeOperation = 'lighter';
            ctx.shadowColor = cc;
            ctx.shadowBlur = 14;
            ctx.strokeStyle = cc;
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.moveTo(l.x - l.vx * 0.05, l.y - l.vy * 0.05);
            ctx.lineTo(l.x + l.vx * 0.05, l.y + l.vy * 0.05);
            ctx.stroke();
            ctx.beginPath();
            ctx.arc(l.x, l.y, 4, 0, Math.PI * 2);
            ctx.fillStyle = cc;
            ctx.fill();
            ctx.restore();
        });

        // 棱镜
        prisms.forEach(function (pr) {
            if (pr.dead) return;
            var cols = [['#409eff', '#a3c4ff'], ['#67c23a', '#b7e6a5'], ['#e6a23c', '#ffd18a']];
            var c0 = cols[pr.type][0], c1 = cols[pr.type][1];
            ctx.save();
            ctx.shadowColor = c0;
            ctx.shadowBlur = pr.hitT > 0 ? 22 : 12;
            // 三角棱镜
            ctx.fillStyle = pr.hitT > 0 ? '#fff' : c1;
            ctx.strokeStyle = c0;
            ctx.lineWidth = 2;
            var s = pr.r || 12;
            ctx.beginPath();
            ctx.moveTo(pr.x, pr.y - s);
            ctx.lineTo(pr.x + s * 0.9, pr.y + s * 0.6);
            ctx.lineTo(pr.x - s * 0.9, pr.y + s * 0.6);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            ctx.restore();
            if (pr.hitT > 0) pr.hitT -= 0.05;
            // 类型标记
            ctx.fillStyle = '#0a0a18';
            ctx.font = '10px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(pr.type === 0 ? '◢' : pr.type === 1 ? '⌒' : '✚', pr.x, pr.y + 3);
        });

        // 碎片
        fragments.forEach(function (f) {
            var g = f.type === 'frag' ? '#ffd76a' : '#7ef0ff';
            ctx.save();
            ctx.shadowColor = g;
            ctx.shadowBlur = 18;
            ctx.fillStyle = g;
            ctx.beginPath();
            if (f.type === 'frag') {
                // 菱形星
                ctx.moveTo(f.x, f.y - f.r);
                ctx.lineTo(f.x + f.r * 0.6, f.y);
                ctx.lineTo(f.x, f.y + f.r);
                ctx.lineTo(f.x - f.r * 0.6, f.y);
                ctx.closePath();
            } else {
                ctx.arc(f.x, f.y, f.r, 0, Math.PI * 2);
            }
            ctx.fill();
            ctx.restore();
        });

        // 玩家(白点, 光标)
        var pr = player.r + (player.speedBoost > 0 ? 2 : 0);
        ctx.save();
        ctx.shadowColor = player.speedBoost > 0 ? '#7ef0ff' : '#fff';
        ctx.shadowBlur = 16;
        ctx.fillStyle = player.speedBoost > 0 ? '#bffcff' : '#fff';
        ctx.beginPath();
        ctx.arc(player.x, player.y, pr, 0, Math.PI * 2);
        ctx.fill();
        ctx.strokeStyle = player.speedBoost > 0 ? '#3fd9ff' : '#c0c0c0';
        ctx.lineWidth = 2;
        ctx.stroke();
        ctx.restore();
    }

    function renderHud() {
        hLevel.textContent = level;
        hTime.textContent = Math.max(0, Math.ceil(levelLeft));
        hLives.textContent = '♥ ' + lives;
        hPrism.textContent = prismCount;
        hScore.textContent = score;
    }

    function syncPrismUI() {
        hPrism.textContent = prismCount;
    }

    // ============ 结束/过关 ============
    function finishLevel() {
        running = false;
        won = true;
        cancelAnimationFrame(raf);
        overlay.classList.add('show');
        ovTitle.textContent = '🎉 关卡 ' + level + ' 通过！';
        ovDesc.textContent = '撑过了 ' + LEVEL_SECONDS + ' 秒！下一关激光更快、更多。当前碎片：' + score +
            '，生命：' + lives + '。准备好迎接更疯狂的弹幕了吗？';
        ovBtn.textContent = '进入下一关 ▶';
        showToast('🏆 通关！');
    }

    function endGame(wonAll) {
        running = false;
        cancelAnimationFrame(raf);
        overlay.classList.add('show');
        ovTitle.textContent = wonAll ? '🏆 恭喜通关！' : '💀 被激光吞没…';
        ovDesc.textContent = wonAll
            ? '你成功穿越了所有光影迷阵！'
            : '存活 ' + Math.floor(elapsed) + ' 秒 · 到达第 ' + level + ' 关 · 收集碎片 ' + score + ' 个';
        ovBtn.textContent = '↺ 重新开始';
    }

    // 统一按钮：通关则进入下一关，失败则重开第 1 关
    ovBtn.onclick = function () {
        if (won) {
            // 进入下一关
            level++;
            resetAll();
            startGame(false);
        } else {
            startGame(true);
        }
    };
    btnStart.onclick = function () { startGame(true); };

    // ============ 输入 ============
    // 鼠标移动 = 玩家位置
    var rect = null;
    function canvasPos(e) {
        if (!rect) rect = canvas.getBoundingClientRect();
        var scaleX = W / rect.width, scaleY = H / rect.height;
        return { x: (e.clientX - rect.left) * scaleX, y: (e.clientY - rect.top) * scaleY };
    }
    canvas.addEventListener('mousemove', function (e) {
        var p = canvasPos(e);
        player.x = Math.max(8, Math.min(W - 8, p.x));
        player.y = Math.max(8, Math.min(H - 8, p.y));
    });

    // 点击放置棱镜(仅左键；右键用于移除)
    canvas.addEventListener('mousedown', function (e) {
        if (e.button !== 0) return;      // 忽略右键
        if (!running) return;
        var p = canvasPos(e);
        if (prismCount <= 0) { showToast('棱镜不足，等待恢复'); return; }
        // 别点在玩家身上/太近
        var d = Math.hypot(p.x - player.x, p.y - player.y);
        if (d < 24) { showToast('棱镜不能放自己身上'); return; }
        // 别叠在已有棱镜上
        for (var i = 0; i < prisms.length; i++) {
            if (Math.hypot(prisms[i].x - p.x, prisms[i].y - p.y) < 22) { showToast('这里已有棱镜'); return; }
        }
        prismCount--;
        prisms.push({ x: p.x, y: p.y, r: 13, type: selType, hitT: 0, cool: 0, turn: Math.random() < 0.5 ? 1 : -1, dead: false });
        syncPrismUI();
    });

    // 滚轮切换类型
    canvas.addEventListener('wheel', function (e) {
        e.preventDefault();
        var d = e.deltaY > 0 ? 1 : -1;
        selType = (selType + d + 3) % 3;
        syncPrismType();
    });
    // 右键移除最近
    canvas.addEventListener('contextmenu', function (e) { e.preventDefault(); removeLast(); });

    // 键盘: Q/E 切换, R 移除
    document.addEventListener('keydown', function (e) {
        var k = e.key.toLowerCase();
        if (k === 'q') { selType = (selType + 2) % 3; syncPrismType(); }
        else if (k === 'e') { selType = (selType + 1) % 3; syncPrismType(); }
        else if (k === 'r') { removeLast(); }
    });

    function removeLast() {
        if (prisms.length === 0) return;
        prisms.pop();
        if (prismCount < PRISM_MAX) prismCount++;
        syncPrismUI();
    }

    function syncPrismType() {
        prismBtns.forEach(function (b) {
            b.classList.toggle('active', parseInt(b.dataset.t, 10) === selType);
        });
    }
    prismBtns.forEach(function (b) {
        b.addEventListener('click', function () { selType = parseInt(b.dataset.t, 10); syncPrismType(); });
    });

    // 玩法说明折叠
    helpbox.style.display = 'none';
    btnGuide.addEventListener('click', function () {
        helpbox.style.display = helpbox.style.display === 'none' ? 'block' : 'none';
    });

    // 重绘尺寸适配
    function fit() {
        rect = null;
    }
    window.addEventListener('resize', fit);
    // 初始画一帧背景
    draw();
})();
</script>
</body>
</html>
