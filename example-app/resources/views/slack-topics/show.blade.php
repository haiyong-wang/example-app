@php
    $user = auth()->user();
    $comments = $topic->comments;
@endphp
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $topic->title }} - 摸鱼话题</title>
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
        .sidebar { width: 200px; background: #fff; border-right: 1px solid #ebeef5; flex-shrink: 0; }
        .sidebar-logo { padding: 18px 20px; border-bottom: 1px solid #ebeef5; font-weight: bold; font-size: 15px; color: #303133; }
        .menu { padding: 8px 0; }
        .menu-item { display: block; padding: 12px 20px; color: #303133; cursor: pointer; border-left: 3px solid transparent; }
        .menu-item:hover { background: #f5f7fa; }
        .menu-item.active { background: #ecf5ff; color: #409eff; border-left-color: #409eff; }

        .main { flex: 1; min-width: 0; }
        .topbar { background: #fff; padding: 10px 20px; border-bottom: 1px solid #ebeef5; font-size: 13px; color: #606266; }
        .topbar .sep { color: #c0c4cc; margin: 0 6px; }
        .topbar a.crumb:hover { color: #e89b26; }
        .tab-content { padding: 20px; }

        .card { background: #fff; border-radius: 8px; margin-bottom: 16px; border: 1px solid #ebeef5; }
        .card-title { padding: 14px 20px; border-bottom: 1px solid #ebeef5; font-weight: 500; font-size: 14px; color: #303133; position: relative; padding-left: 28px; }
        .card-title::before { content: ""; position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 4px; height: 14px; background: #e89b26; border-radius: 2px; }
        .card-body { padding: 20px; }

        .alert { padding: 11px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; }
        .alert-success { background: #f0f9eb; color: #67c23a; border: 1px solid #e1f3d8; }
        .alert-error { background: #fef0f0; color: #f56c6c; border: 1px solid #fde2e2; }

        /* 返回按钮 */
        .btn-back { display: inline-block; margin-bottom: 16px; font-size: 13px; color: #606266; background: #fff; border: 1px solid #ebeef5; padding: 8px 16px; border-radius: 6px; }
        .btn-back:hover { color: #e89b26; border-color: #e89b26; }

        /* 话题主体横幅 */
        .topic-header {
            background: linear-gradient(135deg, #f2b84b 0%, #e89b26 60%, #d9822b 100%);
            border-radius: 10px;
            padding: 26px 28px;
            margin-bottom: 16px;
            color: #fff;
        }
        .topic-header .date-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.22);
            border-radius: 14px;
            padding: 3px 12px;
            font-size: 12px;
            margin-bottom: 12px;
        }
        .topic-header h1 { font-size: 22px; font-weight: 700; margin-bottom: 10px; }
        .topic-header .desc { font-size: 14px; opacity: .95; line-height: 1.8; white-space: pre-wrap; word-break: break-word; }
        .topic-header .meta { margin-top: 16px; font-size: 12px; opacity: .9; }

        /* 意见区 */
        .comment-list { list-style: none; }
        .comment-item { padding: 16px 0; border-bottom: 1px solid #f5f7fa; }
        .comment-item:last-child { border-bottom: none; }
        .comment-head { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; flex-wrap: wrap; }
        .comment-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, #f2b84b, #e89b26);
            color: #fff; font-weight: 600;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 14px;
        }
        .comment-avatar.me { background: linear-gradient(135deg, #409eff, #337ecc); }
        .comment-name { font-size: 14px; font-weight: 600; color: #303133; }
        .comment-name .me-tag { font-size: 11px; background: #ecf5ff; color: #409eff; border-radius: 8px; padding: 1px 8px; margin-left: 4px; }
        .comment-time { font-size: 12px; color: #c0c4cc; }
        .comment-content { font-size: 14px; color: #4b5563; line-height: 1.9; white-space: pre-wrap; word-break: break-word; padding-left: 44px; }

        .empty { text-align: center; color: #c0c4cc; padding: 30px 0; font-size: 13px; }

        /* 发言表单 */
        .reply-field { border: 1px solid #dcdfe6; border-radius: 6px; width: 100%; padding: 10px 12px; font-family: inherit; font-size: 13px; resize: vertical; min-height: 90px; color: #303133; outline: none; transition: border-color .2s; }
        .reply-field:focus { border-color: #e89b26; }
        .field-error { border-color: #f56c6c !important; }
        .error-text { color: #f56c6c; font-size: 12px; margin-top: 6px; }
        .reply-bar { display: flex; justify-content: flex-end; margin-top: 10px; align-items: center; gap: 12px; }
        .char-count { font-size: 12px; color: #c0c4cc; }
        .btn-submit { border: none; background: linear-gradient(135deg, #f2b84b, #e89b26); color: #fff; font-size: 14px; padding: 9px 22px; border-radius: 6px; cursor: pointer; transition: opacity .2s; }
        .btn-submit:hover { opacity: .88; }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
            .comment-content { padding-left: 0; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'slack-topic-show'])

    <main class="main">
        <div class="topbar">
            <a class="crumb" href="{{ route('slack-topics.list') }}">全部话题列表</a>
            <span class="sep">/</span>
            {{ $topic->title }}
        </div>

        <div class="tab-content">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <a class="btn-back" href="{{ route('slack-topics.list') }}">← 返回话题列表</a>

            <!-- 话题主体 -->
            <div class="topic-header">
                <span class="date-chip">📅 {{ $topic->topic_date->format('Y 年 m 月 d 日') }}</span>
                <h1>{{ $topic->title }}</h1>
                @if ($topic->description)
                    <div class="desc">{{ $topic->description }}</div>
                @endif
                <div class="meta">
                    发起人：{{ $topic->user ? $topic->user->name : '已注销' }}
                    · 共 {{ $comments->count() }} 条意见
                </div>
            </div>

            <!-- 讨论详情（谁发表了什么意见） -->
            <div class="card">
                <div class="card-title">讨论详情（谁说了啥都在这里）</div>
                <div class="card-body" style="padding: 0 20px">
                    @if ($comments->isEmpty())
                        <div class="empty">这个话题还没有人发言，快来抢个沙发说说你的看法 🐟</div>
                    @else
                        <ul class="comment-list">
                            @foreach ($comments as $comment)
                                @php $isMe = $comment->user_id === $user->id; @endphp
                                <li class="comment-item">
                                    <div class="comment-head">
                                        <div class="comment-avatar {{ $isMe ? 'me' : '' }}">{{ $comment->user ? mb_substr($comment->user->name, 0, 1) : '?' }}</div>
                                        <div class="comment-name">
                                            {{ $comment->user ? $comment->user->name : '已注销' }}
                                            @if ($isMe)
                                                <span class="me-tag">我</span>
                                            @endif
                                        </div>
                                        <div class="comment-time">{{ $comment->created_at->format('m-d H:i') }}</div>
                                    </div>
                                    <div class="comment-content">{{ $comment->content }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- 发表意见 -->
            <div class="card">
                <div class="card-title">补充你的意见</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('slack-topics.comments.store', $topic) }}">
                        @csrf
                        <textarea name="content" id="commentInput" class="reply-field @error('content') field-error @enderror" maxlength="2000" placeholder="有什么想说的，尽管开麦……">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                        <div class="reply-bar">
                            <span class="char-count"><span id="charNum">0</span> / 2000</span>
                            <button type="submit" class="btn-submit">发表意见</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
(function () {
    var input = document.getElementById('commentInput');
    var num = document.getElementById('charNum');
    if (input && num) {
        var update = function () { num.textContent = input.value.length; };
        input.addEventListener('input', update);
        update();
    }
})();
</script>
</body>
</html>
