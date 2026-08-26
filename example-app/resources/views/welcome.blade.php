<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', '首页') }} - 首页</title>
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

        /* 页面标题 */
        .page-title {
            background: #fff;
            padding: 16px 20px;
            font-weight: 500;
            font-size: 15px;
            border-bottom: 1px solid #ebeef5;
        }

        /* 卡片 */
        .card {
            background: #fff;
            border-radius: 4px;
            margin-bottom: 16px;
        }
        .card-title {
            padding: 14px 20px;
            border-bottom: 1px solid #ebeef5;
            font-weight: 500;
            font-size: 14px;
            color: #303133;
            position: relative;
            padding-left: 28px;
        }
        .card-title::before {
            content: "";
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 14px;
            background: #409eff;
            border-radius: 2px;
        }
        .card-body { padding: 20px; }
        .card-tip {
            font-size: 12px;
            color: #909399;
            margin-left: 10px;
        }

        /* 欢迎横幅 */
        .hero {
            background: linear-gradient(135deg, #409eff 0%, #79bbff 100%);
            border-radius: 4px;
            padding: 40px 32px;
            margin-bottom: 16px;
            color: #fff;
        }
        .hero h1 { font-size: 24px; font-weight: 600; margin-bottom: 12px; }
        .hero p { font-size: 14px; opacity: 0.92; line-height: 1.8; max-width: 680px; }
        .hero-btn {
            display: inline-block;
            margin-top: 24px;
            padding: 10px 28px;
            background: #fff;
            color: #409eff;
            font-size: 14px;
            font-weight: 500;
            border-radius: 3px;
            transition: all .2s;
        }
        .hero-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.12); }

        /* 功能入口网格 */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .feature-card {
            background: #fff;
            border-radius: 4px;
            padding: 24px 20px;
            border: 1px solid #ebeef5;
            transition: all .2s;
            cursor: pointer;
        }
        .feature-card:hover {
            border-color: #409eff;
            box-shadow: 0 4px 12px rgba(64, 158, 255, .12);
            transform: translateY(-2px);
        }
        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 14px;
            background: #ecf5ff;
            color: #409eff;
        }
        .feature-name { font-size: 15px; font-weight: 500; color: #303133; margin-bottom: 6px; }
        .feature-desc { font-size: 13px; color: #909399; line-height: 1.7; }

        /* 快捷入口说明 */
        .info-list { list-style: none; }
        .info-list li {
            position: relative;
            padding: 10px 0 10px 22px;
            border-bottom: 1px dashed #ebeef5;
            color: #606266;
            line-height: 1.7;
        }
        .info-list li:last-child { border-bottom: none; }
        .info-list li::before {
            content: "";
            position: absolute;
            left: 4px;
            top: 17px;
            width: 6px;
            height: 6px;
            background: #409eff;
            border-radius: 50%;
        }
        .info-list li a { color: #409eff; }
        .info-list li a:hover { text-decoration: underline; }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
            .feature-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="layout">

    <!-- 左侧菜单 -->
    <aside class="sidebar">
        <div class="sidebar-logo">⚡ 焦皮的大项目</div>
        <div class="menu">
            <a class="menu-item active" href="{{ url('/') }}">🏠 首页</a>
            <a class="menu-item" href="{{ url('/reports/daily') }}">📊 机型筛选</a>
        </div>
    </aside>

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            首页
            <span class="sep">/</span>
            首页概览
        </div>

        <!-- Tab -->
        <div class="tabs">
            <a class="tab active" href="#">首页</a>
            <a class="tab" href="{{ url('/reports/daily') }}">机型筛选</a>
        </div>

        <!-- 页面标题 -->
        <div class="page-title">首页概览</div>

        <div class="tab-content">

            <!-- 欢迎横幅 -->
            <div class="hero">
                <h1>欢迎使用 焦皮的大项目</h1>
                <p>
                    这是一个统一管理平台，集中提供机型数据查询、统计与分析能力。
                    通过下面的功能入口，您可以快速进入对应模块，查看实时数据与日报报表。
                </p>
                <a class="hero-btn" href="{{ url('/reports/daily') }}">前往机型筛选 →</a>
            </div>

            <!-- 功能入口 -->
            <div class="card">
                <div class="card-title">
                    功能入口
                    <span class="card-tip">点击卡片即可进入对应模块</span>
                </div>
                <div class="card-body">
                    <div class="feature-grid">
                        <a class="feature-card" href="{{ url('/reports/daily') }}">
                            <div class="feature-icon">📱</div>
                            <div class="feature-name">机型筛选</div>
                            <div class="feature-desc">按日期查看每日查询数、命中数、命中比例等统计报表。</div>
                        </a>
                        <a class="feature-card" href="{{ url('/reports/daily') }}">
                            <div class="feature-icon">📊</div>
                            <div class="feature-name">日报统计</div>
                            <div class="feature-desc">查看当日实时数据与历史日报明细，支持日期区间筛选。</div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 平台说明 -->
            <div class="card">
                <div class="card-title">平台说明</div>
                <div class="card-body">
                    <ul class="info-list">
                        <li>本平台用于展示手机型号查询相关的数据报表。</li>
                        <li>日报页面支持按日期区间筛选，以及每页条数调整。</li>
                        <li>命中比例 = 命中数 ÷ 查询数 × 100%，用于衡量匹配效率。</li>
                        <li>如需查看详细报表，请前往 <a href="{{ url('/reports/daily') }}">机型筛选</a> 页面。</li>
                    </ul>
                </div>
            </div>

        </div>

    </main>
</div>
</body>
</html>
