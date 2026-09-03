<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>产品中心 - {{ config('app.name', 'Laravel') }}</title>
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

        /* 产品列表 */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
        .product-card {
            background: #fff;
            border: 1px solid #ebeef5;
            border-radius: 4px;
            overflow: hidden;
            transition: all .2s;
        }
        .product-card:hover { border-color: #409eff; box-shadow: 0 4px 12px rgba(64, 158, 255, .12); }
        .product-thumb {
            height: 180px;
            background: linear-gradient(135deg, #f0f2f5 0%, #e8ebf0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 56px;
            position: relative;
        }
        .product-status {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 2px 10px;
            font-size: 12px;
            border-radius: 10px;
            background: #67c23a;
            color: #fff;
        }
        .product-body { padding: 16px; }
        .product-name { font-size: 16px; font-weight: 600; color: #303133; }
        .product-brand { color: #909399; font-size: 12px; margin-top: 4px; }
        .product-desc {
            color: #606266;
            font-size: 13px;
            line-height: 1.7;
            margin: 10px 0;
        }
        .product-specs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            border-top: 1px dashed #ebeef5;
            padding-top: 12px;
            margin-top: 4px;
        }
        .spec-item { font-size: 13px; color: #606266; }
        .spec-item span { color: #303133; font-weight: 500; }
        .product-price {
            margin-top: 12px;
            color: #f56c6c;
            font-size: 18px;
            font-weight: 600;
        }
        .product-price small { font-size: 12px; font-weight: 400; color: #909399; }
        .product-tags { margin-top: 10px; }
        .tag {
            display: inline-block;
            padding: 2px 8px;
            margin-right: 6px;
            margin-bottom: 4px;
            font-size: 12px;
            background: #ecf5ff;
            color: #409eff;
            border-radius: 3px;
        }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
            .product-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'products'])

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            套餐
            <span class="sep">/</span>
            产品中心
            <span class="sep">/</span>
            手机产品
        </div>

        <div class="tab-content">

            <!-- 产品列表 -->
            <div class="product-grid">
                @foreach ($products as $product)
                <div class="product-card">
                    <div class="product-thumb">
                        📱
                        <span class="product-status">{{ $product['status'] }}</span>
                    </div>
                    <div class="product-body">
                        <div class="product-name">{{ $product['name'] }}</div>
                        <div class="product-brand">{{ $product['brand'] }} · {{ $product['series'] }}</div>
                        <div class="product-desc">{{ $product['desc'] }}</div>
                        <div class="product-specs">
                            <div class="spec-item">型号 <span>{{ $product['model'] }}</span></div>
                            <div class="spec-item">颜色 <span>{{ $product['color'] }}</span></div>
                            <div class="spec-item">存储 <span>{{ $product['storage'] }}</span></div>
                            <div class="spec-item">参考价 <span>{{ $product['price'] }} 元</span></div>
                        </div>
                        <div class="product-tags">
                            @foreach ($product['tags'] as $tag)
                                <span class="tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>

    </main>
</div>
</body>
</html>
