@php
    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>创建话题 - {{ config('app.name', '憨憨专属摸鱼网站') }}</title>
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

        .sidebar { width: 200px; background: #fff; border-right: 1px solid #ebeef5; flex-shrink: 0; display: flex; flex-direction: column; }
        .sidebar-logo { padding: 18px 20px; border-bottom: 1px solid #ebeef5; font-weight: bold; font-size: 15px; color: #303133; }

        .main { flex: 1; min-width: 0; }
        .topbar { background: #fff; padding: 10px 20px; border-bottom: 1px solid #ebeef5; font-size: 13px; color: #606266; }
        .topbar .sep { color: #c0c4cc; margin: 0 6px; }
        .topbar a:hover { color: #e89b26; }
        .tab-content { padding: 20px; }

        .card { background: #fff; border-radius: 8px; margin-bottom: 16px; border: 1px solid #ebeef5; }
        .card-title { padding: 14px 20px; border-bottom: 1px solid #ebeef5; font-weight: 500; font-size: 14px; color: #303133; position: relative; padding-left: 28px; }
        .card-title::before { content: ""; position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 4px; height: 14px; background: #e89b26; border-radius: 2px; }
        .card-body { padding: 20px; }

        /* 提示 */
        .alert { padding: 11px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; }
        .alert-info { background: #f4f4f5; color: #909399; border: 1px solid #e9e9eb; }
        .alert-error { background: #fef0f0; color: #f56c6c; border: 1px solid #fde2e2; }
        .alert-error > div + div { margin-top: 4px; }

        /* 表单 */
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; color: #606266; margin-bottom: 6px; }
        .form-group label .req { color: #f56c6c; margin-left: 2px; }
        .form-group input,
        .form-group textarea {
            width: 100%;
            border: 1px solid #dcdfe6;
            border-radius: 6px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            color: #303133;
            outline: none;
            background: #fff;
            transition: border-color .2s;
        }
        .form-group input:focus,
        .form-group textarea:focus { border-color: #e89b26; }
        .form-group textarea { resize: vertical; min-height: 120px; line-height: 1.6; }
        .form-group .hint { font-size: 12px; color: #c0c4cc; margin-top: 6px; }
        .form-group.has-error input,
        .form-group.has-error textarea { border-color: #f56c6c; }
        .form-group .error-text { color: #f56c6c; font-size: 12px; margin-top: 6px; }
        .form-group.has-error .error-text { display: block; }

        /* 提交按钮 */
        .form-actions { display: flex; align-items: center; gap: 12px; padding-top: 4px; }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #f2b84b, #e89b26);
            border: none;
            color: #fff;
            padding: 10px 26px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: opacity .2s, transform .15s;
        }
        .btn-primary:hover { opacity: .9; transform: translateY(-1px); }
        .btn-back { color: #909399; font-size: 13px; }
        .btn-back:hover { color: #e89b26; }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'slack-topics-new'])

    <main class="main">
        <div class="topbar">
            <a href="{{ route('slack-topics.list') }}">摸鱼话题</a>
            <span class="sep">/</span>
            <a href="{{ route('slack-topics.list') }}">全部话题列表</a>
            <span class="sep">/</span>
            创建话题
        </div>

        <div class="tab-content">

            @if ($errors->any())
                <div class="alert alert-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="card">
                <div class="card-title">创建新话题</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('slack-topics.store') }}">
                        @csrf

                        <div class="form-group {{ $errors->has('topic_date') ? 'has-error' : '' }}">
                            <label for="topic_date">话题日期<span class="req">*</span></label>
                            <input type="date" id="topic_date" name="topic_date"
                                   value="{{ old('topic_date', $defaultDate) }}" required>
                            @if ($errors->has('topic_date'))
                                <div class="error-text">{{ $errors->first('topic_date') }}</div>
                            @else
                                <div class="hint">通常选择今天；同一天可以创建多个话题。</div>
                            @endif
                        </div>

                        <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                            <label for="title">话题标题<span class="req">*</span></label>
                            <input type="text" id="title" name="title"
                                   value="{{ old('title') }}"
                                   maxlength="100" required placeholder="例如：今天中午吃什么？">
                            @if ($errors->has('title'))
                                <div class="error-text">{{ $errors->first('title') }}</div>
                            @endif
                        </div>

                        <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                            <label for="description">背景说明（选填）</label>
                            <textarea id="description" name="description" maxlength="2000"
                                      placeholder="补充一些背景，方便大家更好地开聊……">{{ old('description') }}</textarea>
                            @if ($errors->has('description'))
                                <div class="error-text">{{ $errors->first('description') }}</div>
                            @else
                                <div class="hint">最多 2000 个字。</div>
                            @endif
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">发布话题</button>
                            <a class="btn-back" href="{{ route('slack-topics.list') }}">取消</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>
</body>
</html>
