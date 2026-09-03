<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>小游戏 - {{ config('app.name', 'Laravel') }}</title>
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

        /* Tab */
        .tabs {
            background: #fff;
            padding: 0 20px;
            border-bottom: 1px solid #ebeef5;
            display: flex;
        }
        .tab {
            padding: 14px 16px;
            margin-right: 4px;
            color: #606266;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        .tab.active {
            color: #409eff;
            border-bottom-color: #409eff;
        }
        .tab-content { padding: 20px; }

        /* 小游戏首页说明 */
        .intro {
            background: #fff;
            border-radius: 4px;
            border: 1px solid #ebeef5;
            padding: 16px 20px;
            margin-bottom: 16px;
            color: #606266;
            line-height: 1.7;
        }
        .intro strong { color: #303133; }

        /* 游戏卡片网格 */
        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
        }
        .game-card {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: #fff;
            border: 1px solid #ebeef5;
            border-radius: 6px;
            padding: 20px;
            transition: all .2s;
            position: relative;
        }
        a.game-card { cursor: pointer; }
        a.game-card:hover {
            border-color: #409eff;
            box-shadow: 0 6px 18px rgba(64, 158, 255, .12);
            transform: translateY(-2px);
        }
        .game-card.locked { cursor: not-allowed; opacity: .82; }
        .game-card .ico {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: linear-gradient(135deg, #409eff, #53a8ff);
            color: #fff;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .game-card.gold .ico    { background: linear-gradient(135deg, #f2b84b, #e89b26); }
        .game-card.green .ico   { background: linear-gradient(135deg, #67c23a, #85ce61); }
        .game-card.blue .ico    { background: linear-gradient(135deg, #409eff, #53a8ff); }
        .game-card.purple .ico  { background: linear-gradient(135deg, #a262e6, #bb85ee); }
        .game-card.pink .ico    { background: linear-gradient(135deg, #f789d6, #f07ac8); }
        .game-card.cyan .ico    { background: linear-gradient(135deg, #25c8c8, #4ad6d6); }
        .game-card .meta { min-width: 0; }
        .game-card .meta .name {
            font-size: 16px;
            font-weight: 600;
            color: #303133;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .game-card .meta .name .state {
            font-size: 11px;
            font-weight: 400;
            color: #909399;
            border: 1px solid #ebeef5;
            border-radius: 8px;
            padding: 0 8px;
            background: #fafbfc;
        }
        .game-card .meta .desc {
            font-size: 13px;
            color: #909399;
            margin-top: 6px;
            line-height: 1.6;
        }
        .game-card .meta .arrow {
            position: absolute;
            right: 18px;
            bottom: 14px;
            font-size: 16px;
            color: #c0c4cc;
        }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
            .game-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'games'])

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            摸鱼游戏
            <span class="sep">/</span>
            小游戏
        </div>

        <div class="tab-content">

            <!-- 说明 -->
            <div class="intro">
                <strong>上班摸鱼专用小游戏</strong>，点击任意游戏即可开玩。更多游戏陆续接入中。
            </div>

            <!-- 游戏卡片 -->
            <div class="game-grid">
                @foreach ($games as $game)
                    @if ($game['ready'])
                    <a class="game-card {{ $game['color'] }}" href="{{ url($game['url']) }}">
                        <span class="ico">{{ $game['emoji'] }}</span>
                        <span class="meta">
                            <span class="name">{{ $game['name'] }}</span>
                            <span class="desc">{{ $game['desc'] }}</span>
                        </span>
                        <span class="arrow">→</span>
                    </a>
                    @else
                    <div class="game-card {{ $game['color'] }} locked" title="即将上线">
                        <span class="ico">{{ $game['emoji'] }}</span>
                        <span class="meta">
                            <span class="name">{{ $game['name'] }} <span class="state">敬请期待</span></span>
                            <span class="desc">{{ $game['desc'] }}</span>
                        </span>
                    </div>
                    @endif
                @endforeach
            </div>

        </div>

    </main>
</div>
</body>
</html>
