<?php

namespace App\Http\Controllers;

use App\Models\SlackTopic;
use App\Models\SlackTopicComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 群聊"摸鱼话题导航"控制器。
 *
 * 提供：
 *  - 话题导航列表：按日期倒序展示每天的话题，含发起人、发言人数等概要
 *  - 新增摸鱼话题（同一天可以创建多个）
 *  - 话题详情：查看该话题下每个成员发表的具体意见
 *  - 话题发言：登录成员在话题下补充自己的意见
 *
 * @package App\Http\Controllers
 */
class SlackTopicController extends Controller
{
    /**
     * 独立的话题列表页：以更完整的形式浏览每一个摸鱼话题。
     *
     * 相比导航首页，这里聚焦于"逐个话题"的完整列表，展示：
     *  发起人、意见条数、参与人数、最后发言时间与发言内容摘要、背景说明等。
     *
     * 支持的搜索条件（GET 参数）：
     *  - date   : 按话题日期精确过滤（YYYY-MM-DD）
     *  - title  : 按话题标题模糊匹配
     *  - author : 按创建人名称模糊匹配
     *
     * GET /slack-topics/list
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function list(Request $request)
    {
        // 搜索条件
        $filters = [
            'date'   => trim((string) $request->query('date', '')),
            'title'  => trim((string) $request->query('title', '')),
            'author' => trim((string) $request->query('author', '')),
        ];

        // 按日期倒序分页列出所有话题，附带发起人
        $query = SlackTopic::with('user')->orderByDesc('topic_date');

        // 日期过滤（精确匹配某一天）
        if ($filters['date'] !== '') {
            $query->whereDate('topic_date', $filters['date']);
        }

        // 话题标题模糊匹配
        if ($filters['title'] !== '') {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        // 创建人模糊匹配：通过 user 关联查询
        if ($filters['author'] !== '') {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['author'] . '%');
            });
        }

        $topics = $query->paginate(15)->withQueryString();

        // 对当前页话题一次性取出意见统计：参与人数 + 最后发言时间 + 意见数
        $topicIds = $topics->pluck('id')->all();
        if ($topicIds) {
            // 每个话题：意见总数
            $counts = SlackTopicComment::whereIn('slack_topic_id', $topicIds)
                ->selectRaw('slack_topic_id, count(*) as total')
                ->groupBy('slack_topic_id')
                ->pluck('total', 'slack_topic_id');

            // 每个话题：参与人数（去重后的发言成员数）
            $participants = SlackTopicComment::whereIn('slack_topic_id', $topicIds)
                ->distinct()
                ->select('slack_topic_id', 'user_id')
                ->get()
                ->groupBy('slack_topic_id')
                ->map->count();

            // 每个话题：最后一条发言时间（用于"最后讨论于"）
            $lastTimes = SlackTopicComment::whereIn('slack_topic_id', $topicIds)
                ->select('slack_topic_id', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('slack_topic_id')
                ->pluck('created_at', 'slack_topic_id');

            // 每个话题：最后一条发言内容摘要
            $lastContents = SlackTopicComment::whereIn('slack_topic_id', $topicIds)
                ->select('slack_topic_id', 'content')
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('slack_topic_id')
                ->pluck('content', 'slack_topic_id');
        } else {
            $counts = collect();
            $participants = collect();
            $lastTimes = collect();
            $lastContents = collect();
        }

        // 把统计挂到每个话题对象上，供视图直接使用
        foreach ($topics as $topic) {
            $topic->comments_count    = (int) $counts->get($topic->id, 0);
            $topic->participants_count= (int) $participants->get($topic->id, 0);
            $topic->last_comment_at   = $lastTimes->get($topic->id);
            $topic->last_content      = $lastContents->get($topic->id);
        }

        return view('slack-topics.list', [
            'topics'  => $topics,
            'filters' => $filters,
        ]);
    }

    /**
     * 新建话题表单页：填写标题、日期、背景说明后提交到 store 保存
     *
     * 默认日期为今天；同一天可以创建多个话题。
     *
     * GET /slack-topics/create
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $today = now()->toDateString();

        return view('slack-topics.create', [
            'defaultDate' => $today,
        ]);
    }

    /**
     * 新增一条每日摸鱼话题
     *
     * POST /slack-topics
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'topic_date'  => ['required', 'date'],
            'title'       => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'topic_date.required' => '请选择话题日期',
            'topic_date.date'     => '话题日期格式不正确',
            'title.required'      => '请填写话题标题',
            'title.max'           => '话题标题最多 100 个字',
            'description.max'     => '背景说明最多 2000 个字',
        ]);

        $topic = SlackTopic::create([
            'topic_date'  => $data['topic_date'],
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'user_id'     => Auth::id(),
        ]);

        // 创建成功后回到列表页，便于立刻看到新话题
        return redirect()->route('slack-topics.list')
            ->with('success', '新话题已就位，快去看看并发表你的第一句意见吧~');
    }

    /**
     * 话题详情：查看讨论内容、谁发表了什么意见，并补充发言
     *
     * GET /slack-topics/{slackTopic}
     *
     * @param  \App\Models\SlackTopic  $slackTopic
     * @return \Illuminate\View\View
     */
    public function show(SlackTopic $slackTopic)
    {
        // 加载发起人 + 所有发言（含各自的发言成员）
        $slackTopic->load('user', 'comments.user');

        return view('slack-topics.show', [
            'topic' => $slackTopic,
        ]);
    }

    /**
     * 在某个话题下发表意见
     *
     * POST /slack-topics/{slackTopic}/comments
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SlackTopic  $slackTopic
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeComment(Request $request, SlackTopic $slackTopic)
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ], [
            'content.required' => '好歹说点什么嘛',
            'content.max'      => '一条意见最多 2000 个字',
        ]);

        SlackTopicComment::create([
            'slack_topic_id' => $slackTopic->id,
            'user_id'        => Auth::id(),
            'content'        => $data['content'],
        ]);

        return back()->with('success', '意见已收录，等待群里继续开聊~');
    }
}
