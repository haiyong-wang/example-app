<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>机型筛选日报 - {{ config('app.name', 'Laravel') }}</title>
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
        .tab-content {
            padding: 20px;
        }

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
        .card-body { padding: 16px 20px; }
        .card-tip {
            font-size: 12px;
            color: #909399;
            margin-left: 10px;
        }

        /* 数据表 */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        table.data-table th {
            background: #fafafa;
            color: #606266;
            text-align: left;
            padding: 12px 16px;
            border-bottom: 1px solid #ebeef5;
            font-weight: 500;
        }
        table.data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #ebeef5;
            color: #303133;
        }
        table.data-table tbody tr:hover { background: #f5f7fa; }

        /* 表头行(用于今日数据/筛选条件那种轻量数据展示) */
        table.simple-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        table.simple-table th {
            background: #fafafa;
            color: #606266;
            text-align: left;
            padding: 12px 16px;
            border-bottom: 1px solid #ebeef5;
            font-weight: 500;
        }
        table.simple-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #ebeef5;
            color: #303133;
        }

        /* 筛选表单 */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 20px;
            border-bottom: 1px solid #ebeef5;
        }
        .filter-bar label { color: #606266; font-size: 13px; }
        .filter-bar input[type="date"], .filter-bar select {
            padding: 6px 10px;
            border: 1px solid #dcdfe6;
            border-radius: 3px;
            background: #fff;
            font-size: 13px;
            outline: none;
            color: #606266;
        }
        .filter-bar input[type="date"]:focus, .filter-bar select:focus {
            border-color: #409eff;
        }
        .btn {
            display: inline-block;
            padding: 6px 14px;
            border: 1px solid #dcdfe6;
            background: #fff;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
            color: #606266;
        }
        .btn:hover { color: #409eff; border-color: #c6e2ff; background: #ecf5ff; }

        /* 分页 */
        .pagination-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 14px 20px;
            gap: 10px;
            color: #606266;
            font-size: 13px;
        }
        .pagination-bar select {
            padding: 5px 10px;
            border: 1px solid #dcdfe6;
            border-radius: 3px;
            background: #fff;
            font-size: 13px;
            outline: none;
        }
    </style>
</head>
<body>
<div class="layout">

    <!-- 左侧菜单 -->
    <aside class="sidebar">
        <div class="sidebar-logo">⚡ 焦皮的大项目</div>
        <div class="menu">
            <a class="menu-item" href="#">🏠 首页</a>
            <div class="menu-section">📦 产品中心</div>
            <a class="menu-item active" href="{{ url('/reports/daily') }}">📱 机型筛选</a>
        </div>
    </aside>

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            套餐
            <span class="sep">/</span>
            产品中心
            <span class="sep">/</span>
            机型筛选
        </div>

        <!-- Tab -->
        <div class="tabs">
            <a class="tab" href="#">套餐</a>
            <a class="tab active" href="#">机型筛选</a>
        </div>

        <!-- 页面标题 -->
        <div class="page-title">对比日报</div>

        <div class="tab-content">

            <!-- 今日数据卡片 -->
            <div class="card">
                <div class="card-title">
                    当日数据
                    <span class="card-tip">正查数实时数据, 每日12点更新, 统计时段为当日00:00-12:00</span>
                </div>
                <div class="card-body" style="padding:0">
                    <table class="simple-table">
                        <thead>
                            <tr>
                                <th style="width:40%">查询数</th>
                                <th style="width:40%">命中数</th>
                                <th>命中比例(%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ number_format($todayTotal) }}</td>
                                <td>{{ number_format($todayHit) }}</td>
                                <td>{{ number_format($todayRate, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 日报明细卡片 -->
            <div class="card">
                <div class="card-title">日报明细</div>

                <!-- 筛选表单 -->
                <form method="get" action="{{ url('/reports/daily') }}" class="filter-bar">
                    <label>日期筛选</label>
                    <input type="date" name="start_date" value="{{ $startDate }}">
                    <span style="color:#c0c4cc;">至</span>
                    <input type="date" name="end_date" value="{{ $endDate }}">
                    <button type="submit" class="btn">查询</button>
                    <a href="{{ url('/reports/daily') }}" class="btn">重置</a>
                </form>

                <!-- 数据表 -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>统计日期</th>
                            <th>查询数</th>
                            <th>命中数</th>
                            <th>命中比例(%)</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if (count($paginator) === 0)
                        <tr><td colspan="4" style="text-align:center;color:#909399;padding:30px;">暂无数据</td></tr>
                    @else
                        @foreach ($paginator as $row)
                            <tr>
                                <td>{{ $row['stat_date'] }}</td>
                                <td>{{ number_format($row['query_count']) }}</td>
                                <td>{{ number_format($row['hit_count']) }}</td>
                                <td>{{ number_format($row['hit_rate'], 2) }}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>

                <!-- 分页 -->
                @if ($paginator->total() > 0)
                <div class="pagination-bar">
                    共 {{ $paginator->total() }} 条
                    <form method="get" action="{{ url('/reports/daily') }}" style="display:inline;">
                        <input type="hidden" name="start_date" value="{{ $startDate }}">
                        <input type="hidden" name="end_date" value="{{ $endDate }}">
                        <select name="per_page" onchange="this.form.submit()">
                            @foreach ([10, 20, 50, 100] as $pp)
                                <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}条/页</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                @endif
            </div>

        </div>

    </main>
</div>
</body>
</html>
