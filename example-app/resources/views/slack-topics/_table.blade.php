{{-- 话题列表的表体片段：用于首页首次渲染 & 异步分页局部刷新 --}}
<span data-topic-total="{{ $topics->total() }}" style="display:none;"></span>

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
        @include('pagination.ajax-paginator', ['paginator' => $topics])
    </div>
@endif
