@php
    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>全部摸鱼话题 - {{ config('app.name', '憨憨专属摸鱼网站') }}</title>
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

        /* 右侧主体 */
        .main { flex: 1; min-width: 0; }
        .topbar { background: #fff; padding: 10px 20px; border-bottom: 1px solid #ebeef5; font-size: 13px; color: #606266; }
        .topbar .sep { color: #c0c4cc; margin: 0 6px; }
        .topbar a.crumb:hover { color: #e89b26; }
        .tab-content { padding: 20px; }

        .card { background: #fff; border-radius: 8px; margin-bottom: 16px; border: 1px solid #ebeef5; }
        .card-title { padding: 14px 20px; border-bottom: 1px solid #ebeef5; font-weight: 500; font-size: 14px; color: #303133; position: relative; padding-left: 28px; }
        .card-title::before { content: ""; position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 4px; height: 14px; background: #e89b26; border-radius: 2px; }
        .card-body { padding: 20px; }

        /* ===== 筛选条 ===== */
        .filter-bar {
            background: #fff;
            border: 1px solid #ebeef5;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: center;
        }
        .filter-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #606266;
        }
        .filter-item label {
            color: #909399;
            white-space: nowrap;
        }
        .filter-item input,
        .filter-item select {
            border: 1px solid #dcdfe6;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
            color: #303133;
            outline: none;
            transition: border-color .15s;
            background: #fff;
        }
        .filter-item input { min-width: 140px; }
        .filter-item input.date { min-width: 150px; }
        .filter-item input:focus,
        .filter-item select:focus { border-color: #e89b26; }

        .filter-actions { display: flex; gap: 10px; margin-left: auto; }
        .btn-search,
        .btn-reset {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 7px 16px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            transition: all .15s;
            border: 1px solid transparent;
        }
        .btn-search {
            background: linear-gradient(135deg, #f2b84b, #e89b26);
            color: #fff;
        }
        .btn-search:hover { opacity: .88; }
        .btn-reset {
            background: #fff;
            border-color: #ebeef5;
            color: #606266;
        }
        .btn-reset:hover { color: #e89b26; border-color: #e89b26; }

        /* 新建话题独立块 */
        .new-topic-bar {
            background: #fff;
            border: 1px solid #ebeef5;
            border-radius: 8px;
            padding: 14px 20px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .new-topic-bar .bar-left {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .new-topic-bar .bar-title {
            font-size: 14px;
            font-weight: 600;
            color: #303133;
        }
        .new-topic-bar .bar-sub {
            font-size: 12px;
            color: #909399;
        }
        .new-topic-bar .bar-sub .num {
            color: #e89b26;
            font-weight: 600;
        }
        .btn-new {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #f2b84b, #e89b26);
            border: none;
            color: #fff;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            flex-shrink: 0;
            transition: opacity .2s, transform .15s;
        }
        .btn-new:hover { opacity: .9; transform: translateY(-1px); }

        /* 表格卡片 */
        .table-card .card-body { padding: 0; }

        /* 表格工具条（订单操作） */
        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            border-bottom: 1px solid #f2f3f5;
            flex-wrap: wrap;
            gap: 10px;
        }
        .toolbar-left { display: flex; gap: 10px; align-items: center; }
        .btn-batch {
            border: 1px solid #ebeef5;
            background: #fff;
            color: #606266;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
        }
        .btn-batch:hover { color: #e89b26; border-color: #e89b26; }
        .toolbar-right { font-size: 13px; color: #909399; }
        .toolbar-right .total-num { color: #e89b26; font-weight: 600; }

        /* ===== 表格 ===== */
        .topic-table { width: 100%; border-collapse: collapse; }
        .topic-table th, .topic-table td { text-align: left; padding: 14px 14px; font-size: 13px; }
        .topic-table th {
            color: #909399;
            font-weight: 500;
            border-bottom: 1px solid #ebeef5;
            background: #fafbfc;
            white-space: nowrap;
        }
        .topic-table td {
            border-bottom: 1px solid #f5f7fa;
            color: #606266;
            vertical-align: top;
        }
        .topic-table tbody tr:hover { background: #fafbfc; }
        .topic-table tbody tr:last-child td { border-bottom: none; }

        .col-checkbox { width: 40px; }
        .col-date    { width: 110px; }
        .col-num     { width: 90px; }
        .col-people  { width: 90px; }
        .col-time    { width: 140px; }
        .col-action  { width: 130px; white-space: nowrap; }

        .cell-date .d {
            font-size: 18px;
            font-weight: 700;
            color: #303133;
            line-height: 1.1;
        }
        .cell-date .w { font-size: 12px; color: #c0c4cc; margin-top: 3px; }
        .cell-date .y { font-size: 11px; color: #c0c4cc; margin-top: 1px; }
        .today-badge {
            font-size: 11px;
            background: #e6a23c;
            color: #fff;
            border-radius: 8px;
            padding: 1px 8px;
            margin-left: 6px;
            vertical-align: middle;
        }

        .cell-topic .t {
            font-size: 14px;
            font-weight: 600;
            color: #303133;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 4px;
        }
        .cell-topic .t a { color: #303133; }
        .cell-topic .t a:hover { color: #e89b26; }
        .cell-topic .desc {
            font-size: 12px;
            color: #909399;
            line-height: 1.6;
            margin: 4px 0 6px;
            max-width: 460px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .cell-topic .last {
            background: #fafbfc;
            border: 1px solid #f2f3f5;
            border-left: 3px solid #e89b26;
            border-radius: 4px;
            padding: 5px 9px;
            font-size: 12px;
            color: #909399;
            line-height: 1.5;
            max-width: 460px;
        }
        .cell-topic .last .t { color: #b0b3ba; margin-right: 4px; }

        .cell-author { white-space: nowrap; }
        .cell-author .author-tag {
            color: #e89b26;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .cell-author .author-tag .avatar {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f2b84b, #e89b26);
            color: #fff;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        .cell-author .sub { font-size: 12px; color: #c0c4cc; margin-top: 4px; }

        .cell-num {
            text-align: center;
        }
        .cell-num .v {
            font-size: 17px;
            font-weight: 600;
            color: #e89b26;
            line-height: 1.1;
        }
        .cell-num .k { font-size: 11px; color: #909399; margin-top: 2px; }

        .cell-time { color: #909399; font-size: 12px; }
        .cell-time .l { color: #606266; }
        .cell-time .s { font-size: 11px; color: #c0c4cc; margin-top: 3px; }

        .cell-action { white-space: nowrap; }
        .btn-sm {
            display: inline-block;
            border: 1px solid #ebeef5;
            color: #606266;
            padding: 6px 14px;
            border-radius: 5px;
            font-size: 13px;
            transition: all .15s;
        }
        .btn-sm:hover { color: #e89b26; border-color: #e89b26; }

        /* 分页 */
        .pagination { display: flex; justify-content: center; gap: 6px; padding: 16px 0; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: 6px 11px; border-radius: 4px; border: 1px solid #ebeef5; color: #606266; font-size: 13px; }
        .pagination a:hover { border-color: #e89b26; color: #e89b26; }
        .pagination .active { background: #e89b26; color: #fff; border-color: #e89b26; }
        .pagination .disabled { color: #c0c4cc; }

        .empty {
            text-align: center;
            color: #c0c4cc;
            padding: 50px 0;
            font-size: 13px;
        }
        .empty a { color: #e89b26; text-decoration: underline; }

        /* 复选框样式 */
        .topic-table input[type="checkbox"] {
            cursor: pointer;
            width: 14px;
            height: 14px;
        }

        /* ===== 内联新建话题表单 ===== */
        .form-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-start; }
        .form-field { flex: 1; min-width: 180px; }
        .form-field label { display: block; font-size: 12px; color: #909399; margin-bottom: 6px; }
        .form-field input, .form-field textarea {
            width: 100%;
            border: 1px solid #dcdfe6;
            border-radius: 6px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            color: #303133;
            outline: none;
            transition: border-color .2s;
            background: #fff;
        }
        .form-field input:focus, .form-field textarea:focus { border-color: #e89b26; }
        .form-field textarea { resize: vertical; min-height: 76px; }
        .field-error { border-color: #f56c6c !important; }
        .error-text { color: #f56c6c; font-size: 12px; margin-top: 6px; }
        .btn-new-lg { margin-top: 22px; padding: 10px 24px; font-size: 14px; border: none; flex-shrink: 0; }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
            .topic-table, .topic-table thead { display: block; }
            .topic-table tr { display: block; border: 1px solid #ebeef5; border-radius: 6px; margin: 8px; padding: 6px; }
            .topic-table td { display: block; border-bottom: 1px solid #f5f7fa; padding: 8px 12px; }
            .topic-table th { display: none; }
            .col-action { width: auto; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'slack-topics-list'])

    <main class="main">
        <div class="topbar">
            <span>摸鱼话题</span>
            <span class="sep">/</span>
            全部话题列表
        </div>

        <div class="tab-content">

            @if (session('success'))
                <div class="alert alert-success" style="padding:11px 16px;border-radius:6px;margin-bottom:16px;font-size:13px;background:#f0f9eb;color:#67c23a;border:1px solid #e1f3d8">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error" style="padding:11px 16px;border-radius:6px;margin-bottom:16px;font-size:13px;background:#fef0f0;color:#f56c6c;border:1px solid #fde2e2">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <!-- 新建话题入口：跳转到独立创建页 -->
            <div class="new-topic-bar">
                <div class="bar-left">
                    <span class="bar-title">🐟 发起一个新的摸鱼话题</span>
                    <span class="bar-sub">当前共有 <span class="num">{{ $topics->total() }}</span> 个话题，点击右侧按钮即可创建（同一天可创建多个）</span>
                </div>
                <a class="btn-new" href="{{ route('slack-topics.create') }}">＋ 创建话题</a>
            </div>

            <!-- 筛选条 -->
            <form method="GET" action="{{ route('slack-topics.list') }}" class="filter-bar">
                <div class="filter-item">
                    <label>日期：</label>
                    <input type="date" class="date" name="date" value="{{ $filters['date'] ?? '' }}">
                </div>
                <div class="filter-item">
                    <label>话题：</label>
                    <input type="text" name="title" value="{{ $filters['title'] ?? '' }}" placeholder="模糊匹配话题标题">
                </div>
                <div class="filter-item">
                    <label>创建人：</label>
                    <input type="text" name="author" value="{{ $filters['author'] ?? '' }}" placeholder="按发起人姓名搜索">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-search">🔍 搜索</button>
                    <a class="btn-reset" href="{{ route('slack-topics.list') }}">↻ 重置</a>
                </div>
            </form>

            <!-- 话题列表表格 -->
            <div class="card table-card">
                <div class="card-title">全部话题（{{ $topics->total() }} 个）</div>

                @if ($topics->isEmpty())
                    <div class="empty">
                        还没有符合条件的话题，
                    </div>
                @else
                    <!-- 表格工具条 -->
                    <div class="table-toolbar">
                        <div class="toolbar-left">
                            <button type="button" class="btn-batch">· 批量操作 ▾</button>
                        </div>
                        <div class="toolbar-right">
                            共 <span class="total-num">{{ $topics->total() }}</span> 个话题，
                            当前第 {{ $topics->currentPage() }} / {{ $topics->lastPage() }} 页
                        </div>
                    </div>

                    <div class="card-body" style="padding: 0;">
                        <form method="POST" action="#" id="batchForm">
                            @csrf
                            <table class="topic-table">
                                <thead>
                                    <tr>
                                        <th class="col-checkbox">
                                            <input type="checkbox" id="checkAll">
                                        </th>
                                        <th class="col-date">话题日期</th>
                                        <th>话题（标题 / 描述 / 最后发言）</th>
                                        <th class="col-people">发起人</th>
                                        <th class="col-num">意见条数</th>
                                        <th class="col-num">参与人数</th>
                                        <th class="col-time">最后讨论</th>
                                        <th class="col-action">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topics as $topic)
                                        @php
                                            $week = ['周日','周一','周二','周三','周四','周五','周六'][(int) $topic->topic_date->dayOfWeek];
                                            $authorName = $topic->user ? $topic->user->name : '已注销';
                                            $authorFirst = $topic->user ? mb_substr($topic->user->name, 0, 1) : '？';
                                        @endphp
                                        <tr>
                                            <td class="col-checkbox">
                                                <input type="checkbox" name="ids[]" value="{{ $topic->id }}">
                                            </td>
                                            <td class="cell-date col-date">
                                                <div class="d">{{ $topic->topic_date->format('d') }}</div>
                                                <div class="w">{{ $week }}</div>
                                                <div class="y">{{ $topic->topic_date->format('Y-m') }}</div>
                                            </td>
                                            <td class="cell-topic">
                                                <div class="t">
                                                    <a href="{{ route('slack-topics.show', $topic) }}">{{ $topic->title }}</a>
                                                    @if ($topic->topic_date->isToday())
                                                        <span class="today-badge">今日话题</span>
                                                    @endif
                                                </div>
                                                @if ($topic->description)
                                                    <div class="desc">{{ $topic->description }}</div>
                                                @endif
                                            </td>
                                            <td class="cell-author col-people">
                                                <span class="author-tag">
                                                    <span class="avatar">{{ $authorFirst }}</span>
                                                    {{ $authorName }}
                                                </span>
                                                <div class="sub">话题 #{{ $topic->id }}</div>
                                            </td>
                                            <td class="cell-num col-num">
                                                <div class="v">{{ $topic->comments_count }}</div>
                                                <div class="k">条意见</div>
                                            </td>
                                            <td class="cell-num col-num">
                                                <div class="v">{{ $topic->participants_count }}</div>
                                                <div class="k">人参与</div>
                                            </td>
                                            <td class="cell-time col-time">
                                                @if ($topic->last_comment_at)
                                                    <div class="l">{{ $topic->last_comment_at->format('Y-m-d H:i') }}</div>
                                                    <div class="s">{{ $topic->last_comment_at->diffForHumans() }}</div>
                                                @else
                                                    <div class="l">—</div>
                                                    <div class="s">尚未讨论</div>
                                                @endif
                                            </td>
                                            <td class="cell-action col-action">
                                                <a class="btn-sm" href="{{ route('slack-topics.show', $topic) }}">查看讨论</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </form>
                        <div class="pagination">
                            {{ $topics->withQueryString()->links() }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </main>
</div>

<script>
    (function () {
        // 全选 / 反选
        var checkAll = document.getElementById('checkAll');
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                var boxes = document.querySelectorAll('.topic-table tbody input[type="checkbox"]');
                Array.prototype.forEach.call(boxes, function (b) {
                    b.checked = checkAll.checked;
                });
            });
        }

    })();
</script>
</body>
</html>
