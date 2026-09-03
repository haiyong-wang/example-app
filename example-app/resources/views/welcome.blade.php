@php
    $user = auth()->user();
    // 简单换算：注册天数 => 等级（憨憨值）
    $days = $user->created_at ? max(1, (int) $user->created_at->diffInDays(now()) + 1) : 1;
    $level = min(99, (int) floor($days / 7) + 1);
    $levelName = $level >= 20 ? '摸鱼宗师' : ($level >= 10 ? '摸鱼高手' : ($level >= 5 ? '资深摸鱼人' : '摸鱼新人'));
    // 今日问候（强制按东八区计算，避免服务器 UTC 导致问候错乱）
    $hour = (int) now()->setTimezone('Asia/Shanghai')->format('G');
    $greet = $hour < 6 ? '夜深了还不睡' : ($hour < 9 ? '早安' : ($hour < 12 ? '上午好' : ($hour < 14 ? '中午好' : ($hour < 18 ? '下午好' : '晚上好'))));
@endphp
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首页 - {{ config('app.name', '憨憨专属摸鱼网站') }}</title>
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
        .menu-section { padding: 10px 20px 6px; color: #909399; font-size: 12px; }

        /* 右侧主体 */
        .main { flex: 1; min-width: 0; }

        /* 顶部 */
        .topbar { background: #fff; padding: 10px 20px; border-bottom: 1px solid #ebeef5; font-size: 13px; color: #606266; }
        .topbar .sep { color: #c0c4cc; margin: 0 6px; }

        /* Tab */
        .tabs { background: #fff; padding: 0 20px; border-bottom: 1px solid #ebeef5; display: flex; }
        .tab { padding: 14px 16px; margin-right: 4px; color: #606266; cursor: pointer; border-bottom: 2px solid transparent; }
        .tab.active { color: #409eff; border-bottom-color: #409eff; }
        .tab-content { padding: 20px; }

        /* 卡片 */
        .card { background: #fff; border-radius: 8px; margin-bottom: 16px; }
        .card-title { padding: 14px 20px; border-bottom: 1px solid #ebeef5; font-weight: 500; font-size: 14px; color: #303133; position: relative; padding-left: 28px; }
        .card-title::before { content: ""; position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 4px; height: 14px; background: #e89b26; border-radius: 2px; }
        .card-body { padding: 20px; }

        /* 欢迎横幅（摸鱼黄） */
        .hero {
            background: linear-gradient(135deg, #f2b84b 0%, #e89b26 60%, #d9822b 100%);
            border-radius: 10px;
            padding: 30px 28px;
            margin-bottom: 16px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
        }
        .hero .big-avatar {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: rgba(255,255,255,.22);
            border: 3px solid rgba(255,255,255,.55);
            color: #fff;
            font-size: 34px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .hero-text { flex: 1; min-width: 240px; }
        .hero-text h1 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
        .hero-text .signature { font-size: 14px; opacity: .95; line-height: 1.7; font-style: italic; }
        .hero-text .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 10px;
            background: rgba(255,255,255,.22);
            border-radius: 14px;
            padding: 4px 12px;
            font-size: 12px;
        }

        /* 个人资料信息 */
        .profile-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px; }
        .profile-item { background: #fafbfc; border: 1px solid #ebeef5; border-radius: 8px; padding: 14px 16px; }
        .profile-item .k { font-size: 12px; color: #909399; margin-bottom: 6px; }
        .profile-item .v { font-size: 14px; color: #303133; font-weight: 500; word-break: break-all; }

        /* 功能入口网格 */
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
        .feature-card {
            background: #fff;
            border-radius: 8px;
            padding: 22px 20px;
            border: 1px solid #ebeef5;
            transition: all .2s;
            cursor: pointer;
            display: block;
        }
        .feature-card:hover { border-color: #e89b26; box-shadow: 0 6px 18px rgba(232, 155, 38, .15); transform: translateY(-2px); }
        .feature-icon {
            width: 44px; height: 44px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; margin-bottom: 14px;
            background: #fdf6ec; color: #e89b26;
        }
        .feature-name { font-size: 15px; font-weight: 600; color: #303133; margin-bottom: 6px; }
        .feature-desc { font-size: 13px; color: #909399; line-height: 1.7; }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
            .feature-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'home'])

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            首页
            <span class="sep">/</span>
            我的摸鱼面板
        </div>

        <div class="tab-content">

            <!-- 欢迎横幅（带用户头像） -->
            <div class="hero">
                <div class="big-avatar">{{ mb_substr($user->name, 0, 1) }}</div>
                <div class="hero-text">
                    <h1>{{ $greet }}，{{ $user->name }} 憨憨 🐟</h1>
                    <div class="signature">“{{ $user->signature ?? '今天也是快乐摸鱼的一天~' }}”</div>
                    <span class="badge">🏅 {{ $levelName }} · Lv.{{ $level }}</span>
                </div>
            </div>

            <!-- 我的信息 -->
            <div class="card">
                <div class="card-title">我的信息</div>
                <div class="card-body">
                    <div class="profile-grid">
                        <div class="profile-item">
                            <div class="k">昵称</div>
                            <div class="v">{{ $user->name }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="k">邮箱</div>
                            <div class="v">{{ $user->email }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="k">注册时间</div>
                            <div class="v">{{ $user->created_at ? $user->created_at->format('Y-m-d') : '-' }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="k">最近登录</div>
                            <div class="v">{{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i') : '刚刚' }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="k">摸鱼宣言</div>
                            <div class="v">{{ $user->signature ?? '还没有宣言' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 摸鱼快捷入口 -->
            <div class="card">
                <div class="card-title">
                    摸鱼快捷入口
                    <span style="font-size:12px;color:#c0c4cc;margin-left:10px;font-weight:400">点击卡片快乐开摸</span>
                </div>
                <div class="card-body">
                    <div class="feature-grid">
                        <a class="feature-card" href="{{ url('/games') }}">
                            <div class="feature-icon">🎮</div>
                            <div class="feature-name">摸鱼小游戏</div>
                            <div class="feature-desc">敲木鱼、打地鼠……总有一款适合开小差。</div>
                        </a>
                        <a class="feature-card" href="{{ url('/tools') }}">
                            <div class="feature-icon">🔧</div>
                            <div class="feature-name">实用小工具</div>
                            <div class="feature-desc">二维码、PDF 转换等摸鱼间隙救急好帮手。</div>
                        </a>
{{--                        <a class="feature-card" href="{{ url('/reports/daily') }}">--}}
{{--                            <div class="feature-icon">📊</div>--}}
{{--                            <div class="feature-name">机型数据日报</div>--}}
{{--                            <div class="feature-desc">顺带瞄一眼的报表数据，显得很忙的样子。</div>--}}
{{--                        </a>--}}
                    </div>
                </div>
            </div>

        </div>

    </main>
</div>
</body>
</html>
