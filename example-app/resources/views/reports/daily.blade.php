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
            <a class="menu-item" href="{{ url('/') }}">🏠 首页</a>
            <a class="menu-item active" href="{{ url('/reports/daily') }}">📊 机型筛选</a>
        </div>
    </aside>

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            套餐
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
                                <td id="today-total">-</td>
                                <td id="today-hit">-</td>
                                <td id="today-rate">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 日报明细卡片 -->
            <div class="card">
                <div class="card-title">日报明细</div>

                <!-- 筛选表单 -->
                <form id="filter-form" class="filter-bar">
                    <label>日期筛选</label>
                    <input type="date" name="start_date">
                    <span style="color:#c0c4cc;">至</span>
                    <input type="date" name="end_date">
                    <button type="submit" class="btn">查询</button>
                    <button type="button" id="btn-reset" class="btn">重置</button>
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
                    <tbody id="report-tbody">
                        <tr>
                            <td colspan="4" style="text-align:center;color:#909399;padding:30px;">
                                <span id="table-status">加载中...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- 分页 -->
                <div id="pagination-bar" class="pagination-bar" style="display:none;">
                    <span id="page-total">共 0 条</span>
                    <button type="button" id="btn-prev" class="btn">上一页</button>
                    <span id="page-current">1/1</span>
                    <button type="button" id="btn-next" class="btn">下一页</button>
                    <select id="per-page">
                        @foreach ([10, 20, 50, 100] as $pp)
                            <option value="{{ $pp }}">{{ $pp }}条/页</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>

    </main>
</div>

<script>
(function () {
    // 状态
    var state = {
        startDate: '',
        endDate: '',
        page: 1,
        perPage: 10
    };

    var tbody = document.getElementById('report-tbody');
    var tableStatus = document.getElementById('table-status');
    var form = document.getElementById('filter-form');
    var startInput = form.querySelector('input[name="start_date"]');
    var endInput = form.querySelector('input[name="end_date"]');
    var paginationBar = document.getElementById('pagination-bar');
    var pageTotal = document.getElementById('page-total');
    var pageCurrent = document.getElementById('page-current');
    var btnPrev = document.getElementById('btn-prev');
    var btnNext = document.getElementById('btn-next');
    var perPageSelect = document.getElementById('per-page');

    function buildQuery(params) {
        var qs = new URLSearchParams();
        if (params.startDate) qs.set('start_date', params.startDate);
        if (params.endDate) qs.set('end_date', params.endDate);
        qs.set('page', params.page);
        qs.set('per_page', params.perPage);
        return qs.toString();
    }

    function numberFormat(n) {
        return Number(n || 0).toLocaleString();
    }

    function renderToday(today) {
        document.getElementById('today-total').textContent = numberFormat(today.total);
        document.getElementById('today-hit').textContent = numberFormat(today.hit);
        document.getElementById('today-rate').textContent = Number(today.rate).toFixed(2);
    }

    function renderList(list) {
        tbody.innerHTML = '';
        if (!list.length) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.setAttribute('colspan', '4');
            td.style.cssText = 'text-align:center;color:#909399;padding:30px;';
            td.textContent = '暂无数据';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }
        list.forEach(function (row) {
            var tr = document.createElement('tr');
            var cells = [
                row.stat_date,
                numberFormat(row.query_count),
                numberFormat(row.hit_count),
                Number(row.hit_rate).toFixed(2)
            ];
            cells.forEach(function (text) {
                var td = document.createElement('td');
                td.textContent = text;
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
    }

    function renderPagination(p) {
        if (!p.total) {
            paginationBar.style.display = 'none';
            return;
        }
        paginationBar.style.display = 'flex';
        pageTotal.textContent = '共 ' + numberFormat(p.total) + ' 条';
        pageCurrent.textContent = p.current + '/' + p.last_page;
        btnPrev.disabled = p.current <= 1;
        btnNext.disabled = p.current >= p.last_page;
    }

    function loadData() {
        tableStatus.textContent = '加载中...';
        var url = '{{ url('/reports/daily/data') }}' + '?' + buildQuery(state);
        fetch(url)
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (json) {
                if (json.code !== 0) throw new Error(json.message || '数据异常');
                renderToday(json.data.today);
                renderList(json.data.list);
                renderPagination(json.data.pagination);
                startInput.value = json.data.filters.start_date || '';
                endInput.value = json.data.filters.end_date || '';
            })
            .catch(function (err) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#f56c6c;padding:30px;">加载失败: ' + err.message + '</td></tr>';
                paginationBar.style.display = 'none';
            });
    }

    // 筛选查询
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        state.startDate = startInput.value;
        state.endDate = endInput.value;
        state.page = 1;
        loadData();
    });

    // 重置
    document.getElementById('btn-reset').addEventListener('click', function () {
        startInput.value = '';
        endInput.value = '';
        state.startDate = '';
        state.endDate = '';
        state.page = 1;
        perPageSelect.value = '10';
        state.perPage = 10;
        loadData();
    });

    // 分页
    btnPrev.addEventListener('click', function () {
        if (state.page > 1) { state.page--; loadData(); }
    });
    btnNext.addEventListener('click', function () {
        state.page++; loadData();
    });
    perPageSelect.addEventListener('change', function () {
        state.perPage = parseInt(perPageSelect.value, 10) || 10;
        state.page = 1;
        loadData();
    });

    // 初始加载
    loadData();
})();
</script>
</body>
</html>
