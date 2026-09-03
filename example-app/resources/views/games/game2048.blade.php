<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2048 - {{ config('app.name', 'Laravel') }}</title>
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

        /* ===== 2048 页面 ===== */
        .wrap {
            max-width: 520px;
            margin: 0 auto;
        }
        .card {
            background: #fff;
            border: 1px solid #ebeef5;
            border-radius: 8px;
            overflow: hidden;
        }
        .card-head {
            padding: 16px 20px;
            text-align: center;
            background: linear-gradient(135deg, #e3f4fb, #d0edf8);
            border-bottom: 1px solid #c8e5f0;
        }
        .card-head .title { font-size: 16px; font-weight: 600; color: #27657a; }
        .card-head .sub { font-size: 13px; color: #4c92ac; margin-top: 2px; }

        .card-body { padding: 20px; }

        /* 状态栏 */
        .state-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }
        .state-bar .pills { display: flex; gap: 10px; }
        .pill {
            background: #eef2f6;
            border: 1px solid #e2e8ef;
            border-radius: 6px;
            padding: 6px 12px;
            text-align: center;
            min-width: 76px;
        }
        .pill .l { font-size: 11px; color: #909399; display: block; }
        .pill .v {
            font-size: 18px;
            font-weight: 700;
            color: #303133;
            font-variant-numeric: tabular-nums;
        }
        .pill.best .v { color: #e6a23c; }
        .btn {
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            color: #fff;
            background: #e6a23c;
        }
        .btn:hover { background: #ebb563; }

        /* 游戏棋盘 */
        .board-wrap {
            position: relative;
            background: #bbada0;
            border-radius: 8px;
            padding: 10px;
            touch-action: none;
        }
        .board {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(4, 1fr);
            gap: 10px;
            aspect-ratio: 1 / 1;
        }
        .cell {
            position: relative;
            background: #cdc1b4;
            border-radius: 6px;
        }
        .tile {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: 700;
            color: #776e65;
            background: #eee4da;
            border-radius: 6px;
            animation: pop .16s ease;
            font-variant-numeric: tabular-nums;
        }
        @keyframes pop {
            0% { transform: scale(.5); opacity: .4; }
            100% { transform: scale(1); opacity: 1; }
        }
        .tile.t-2   { background: #eee4da; color: #776e65; }
        .tile.t-4   { background: #ede0c8; color: #776e65; }
        .tile.t-8   { background: #f2b179; color: #f9f6f2; }
        .tile.t-16  { background: #f59563; color: #f9f6f2; }
        .tile.t-32  { background: #f67c5f; color: #f9f6f2; }
        .tile.t-64  { background: #f65e3b; color: #f9f6f2; }
        .tile.t-128 { background: #edcf72; color: #f9f6f2; font-size: 26px; }
        .tile.t-256 { background: #edcc61; color: #f9f6f2; font-size: 26px; }
        .tile.t-512 { background: #edc850; color: #f9f6f2; font-size: 26px; }
        .tile.t-1024{ background: #edc53f; color: #f9f6f2; font-size: 22px; }
        .tile.t-2048{ background: #edc22e; color: #f9f6f2; font-size: 22px; }
        .tile.t-super { background: #3c3a32; color: #f9f6f2; }

        /* 结束遮罩 */
        .overlay {
            position: absolute;
            inset: 0;
            background: rgba(238, 228, 218, .73);
            border-radius: 8px;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            z-index: 10;
            text-align: center;
            padding: 16px;
        }
        .overlay.show { display: flex; }
        .overlay .result { font-size: 30px; font-weight: 700; color: #776e65; }
        .overlay .tip { font-size: 14px; color: #776e65; }
        .overlay .btn.new { background: #8f7a66; }
        .overlay .btn.new:hover { background: #a08a75; }

        /* 方向控制按钮（移动端用） */
        .dir-pad {
            display: none;
            grid-template-columns: repeat(3, 64px);
            grid-template-rows: repeat(3, 48px);
            gap: 8px;
            justify-content: center;
            margin-top: 14px;
        }
        .dir-pad button {
            border: 1px solid #e2e8ef;
            background: #fff;
            border-radius: 8px;
            font-size: 18px;
            color: #909399;
            cursor: pointer;
        }
        .dir-pad button:active { background: #eef2f6; }

        .help {
            font-size: 13px;
            color: #909399;
            margin-top: 14px;
            text-align: center;
            line-height: 1.7;
        }

        @media (max-width: 640px) {
            .tile { font-size: 22px; }
            .tile.t-128, .tile.t-256, .tile.t-512 { font-size: 20px; }
            .tile.t-1024, .tile.t-2048 { font-size: 18px; }
            .dir-pad { display: grid; }
        }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => '2048'])

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            摸鱼游戏
            <span class="sep">/</span>
            2048
        </div>

        <div class="tab-content">
            <div class="wrap">
                <div class="card">
                    <div class="card-head">
                        <div class="title">🧩 2048 · 数字合体术</div>
                        <div class="sub">把方块一路合到 2048，越合越大越上头</div>
                    </div>
                    <div class="card-body">

                        <!-- 状态栏 -->
                        <div class="state-bar">
                            <div class="pills">
                                <div class="pill">
                                    <span class="l">得分</span>
                                    <span class="v" id="score">0</span>
                                </div>
                                <div class="pill best">
                                    <span class="l">最高分</span>
                                    <span class="v" id="best">0</span>
                                </div>
                            </div>
                            <button type="button" class="btn" id="btn-restart">↺ 重新开始</button>
                        </div>

                        <!-- 棋盘 -->
                        <div class="board-wrap" id="board-wrap">
                            <div class="board" id="board">
                                <div class="cell"></div><div class="cell"></div><div class="cell"></div><div class="cell"></div>
                                <div class="cell"></div><div class="cell"></div><div class="cell"></div><div class="cell"></div>
                                <div class="cell"></div><div class="cell"></div><div class="cell"></div><div class="cell"></div>
                                <div class="cell"></div><div class="cell"></div><div class="cell"></div><div class="cell"></div>
                            </div>
                            <div class="overlay" id="overlay">
                                <div class="result" id="overlay-result">游戏结束</div>
                                <div class="tip" id="overlay-tip">点击按钮重新再来</div>
                                <button type="button" class="btn new" id="btn-new">↺ 再来一局</button>
                            </div>
                        </div>

                        <!-- 移动端方向键 -->
                        <div class="dir-pad" id="dir-pad">
                            <span></span>
                            <button data-dir="up" type="button">▲</button>
                            <span></span>
                            <button data-dir="left" type="button">◀</button>
                            <span></span>
                            <button data-dir="right" type="button">▶</button>
                            <span></span>
                            <button data-dir="down" type="button">▼</button>
                            <span></span>
                        </div>

                        <div class="help">
                            💡 键盘 <b>← ↑ → ↓</b> 或滑动屏幕，相同数字碰撞合并。每次滑动会生成一个新方块，<br>
                            直到填满 4×4 棋盘无法移动即为游戏结束。电脑端直接用方向键开玩。
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
(function () {
    var SIZE = 4;
    var board = [];
    var score = 0;
    var over = false;
    var won = false;
    var best = Number(localStorage.getItem('my2048.best') || 0);

    var scoreEl = document.getElementById('score');
    var bestEl = document.getElementById('best');
    var boardWrap = document.getElementById('board-wrap');
    var overlay = document.getElementById('overlay');
    var overlayResult = document.getElementById('overlay-result');
    var overlayTip = document.getElementById('overlay-tip');

    function emptyBoard() {
        board = [];
        for (var r = 0; r < SIZE; r++) {
            board.push([0, 0, 0, 0]);
        }
    }

    function reset() {
        emptyBoard();
        score = 0;
        over = false;
        won = false;
        scoreEl.textContent = '0';
        bestEl.textContent = best;
        overlay.classList.remove('show');
        addRandom();
        addRandom();
        render();
    }

    function addRandom() {
        var empty = [];
        for (var r = 0; r < SIZE; r++) {
            for (var c = 0; c < SIZE; c++) {
                if (board[r][c] === 0) empty.push([r, c]);
            }
        }
        if (!empty.length) return;
        var cell = empty[Math.floor(Math.random() * empty.length)];
        board[cell[0]][cell[1]] = Math.random() < 0.9 ? 2 : 4;
    }

    function cloneBoard() {
        return board.map(function (row) { return row.slice(); });
    }

    function boardsEqual(a, b) {
        for (var r = 0; r < SIZE; r++) {
            for (var c = 0; c < SIZE; c++) {
                if (a[r][c] !== b[r][c]) return false;
            }
        }
        return true;
    }

    // 将一行向左侧压缩合并，返回新行与本次合并得分
    function collapse(line) {
        var vals = line.filter(function (v) { return v !== 0; });
        var merged = [];
        var gain = 0;
        for (var i = 0; i < vals.length; i++) {
            if (i + 1 < vals.length && vals[i] === vals[i + 1]) {
                var v = vals[i] * 2;
                merged.push(v);
                gain += v;
                i++;
            } else {
                merged.push(vals[i]);
            }
        }
        while (merged.length < SIZE) merged.push(0);
        return { line: merged, gain: gain };
    }

    // 将所有行统一转为某方向压缩所需的“行方向”，再转回
    function move(dir) {
        var before = cloneBoard();
        var gain = 0;
        var reachable = false;

        function transform() {
            // dir: 0 up,1 right,2 down,3 left
            // 统一按“向左”的逻辑处理，通过转置/翻转映射
        }

        for (var r = 0; r < SIZE; r++) {
            // 提取当前方向下的一条“链”
            var chain = [];
            for (var i = 0; i < SIZE; i++) {
                var row, col;
                switch (dir) {
                    case 0: row = i; col = r; break;      // up
                    case 1: row = r; col = SIZE - 1 - i; break; // right
                    case 2: row = SIZE - 1 - i; col = r; break; // down
                    default: row = r; col = i; break;      // left
                }
                chain.push(board[row][col]);
            }
            var res = collapse(chain);
            gain += res.gain;
            for (var j = 0; j < SIZE; j++) {
                var rr, cc;
                switch (dir) {
                    case 0: rr = j; cc = r; break;
                    case 1: rr = r; cc = SIZE - 1 - j; break;
                    case 2: rr = SIZE - 1 - j; cc = r; break;
                    default: rr = r; cc = j; break;
                }
                board[rr][cc] = res.line[j];
            }
        }

        var changed = !boardsEqual(board, before);
        if (changed) {
            score += gain;
            addRandom();
            updateScore();
            if (checkWin()) return;
            if (!canMove()) {
                gameOver();
            }
        }
        render();
    }

    function updateScore() {
        scoreEl.textContent = score;
        if (score > best) {
            best = score;
            localStorage.setItem('my2048.best', String(best));
            bestEl.textContent = best;
        }
    }

    function checkWin() {
        for (var r = 0; r < SIZE; r++) {
            for (var c = 0; c < SIZE; c++) {
                if (board[r][c] === 2048 && !won) {
                    won = true;
                    overlayResult.textContent = '🎉 达成 2048！';
                    overlayTip.textContent = '太强了，继续挑战更高分吧！';
                    overlay.classList.add('show');
                    return true;
                }
            }
        }
        return false;
    }

    function canMove() {
        for (var r = 0; r < SIZE; r++) {
            for (var c = 0; c < SIZE; c++) {
                if (board[r][c] === 0) return true;
                if (c + 1 < SIZE && board[r][c] === board[r][c + 1]) return true;
                if (r + 1 < SIZE && board[r][c] === board[r + 1][c]) return true;
            }
        }
        return false;
    }

    function gameOver() {
        over = true;
        overlayResult.textContent = '游戏结束';
        overlayTip.textContent = '得分：' + score + (best > 0 ? '，历史最高 ' + best : '');
        overlay.classList.add('show');
    }

    function tileClass(v) {
        if (v === 0) return '';
        if (v > 2048) return 't-super';
        return 't-' + v;
    }

    function render() {
        var cells = boardWrap.querySelectorAll('.cell');
        for (var idx = 0; idx < SIZE * SIZE; idx++) {
            var cell = cells[idx];
            cell.innerHTML = '';
            var r = Math.floor(idx / SIZE);
            var c = idx % SIZE;
            var v = board[r][c];
            if (v === 0) continue;
            var tile = document.createElement('div');
            tile.className = 'tile ' + tileClass(v);
            tile.textContent = v;
            cell.appendChild(tile);
        }
    }

    // 处理键盘 / 滑动 / 方向按钮，若处于结束遮罩但已胜利则可继续
    function tryMove(dir) {
        if (over) return;
        if (!won && overlay.classList.contains('show')) return;
        if (overlay.classList.contains('show') && won) {
            overlay.classList.remove('show');
        }
        move(dir);
    }

    // 键盘
    document.addEventListener('keydown', function (e) {
        var map = { ArrowUp: 0, ArrowDown: 2, ArrowLeft: 3, ArrowRight: 1 };
        if (e.key in map) {
            e.preventDefault();
            tryMove(map[e.key]);
        }
    });

    // 触屏滑动
    var sx = 0, sy = 0, tracking = false;
    var bwrap = document.getElementById('board-wrap');
    bwrap.addEventListener('touchstart', function (e) {
        var t = e.touches[0];
        sx = t.clientX; sy = t.clientY;
        tracking = true;
    }, { passive: true });
    bwrap.addEventListener('touchmove', function (e) {
        e.preventDefault();
    }, { passive: false });
    bwrap.addEventListener('touchend', function (e) {
        if (!tracking) return;
        tracking = false;
        var t = e.changedTouches[0];
        var dx = t.clientX - sx;
        var dy = t.clientY - sy;
        if (Math.abs(dx) < 20 && Math.abs(dy) < 20) return;
        if (Math.abs(dx) > Math.abs(dy)) {
            tryMove(dx > 0 ? 1 : 3);
        } else {
            tryMove(dy > 0 ? 2 : 0);
        }
    }, { passive: true });

    // 方向按钮
    var pad = document.getElementById('dir-pad');
    pad.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-dir]');
        if (!btn) return;
        var dirMap = { up: 0, right: 1, down: 2, left: 3 };
        tryMove(dirMap[btn.getAttribute('data-dir')]);
    });

    // 按钮
    document.getElementById('btn-restart').addEventListener('click', reset);
    document.getElementById('btn-new').addEventListener('click', reset);

    reset();
})();
</script>
</body>
</html>
