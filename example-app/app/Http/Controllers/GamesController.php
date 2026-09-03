<?php

namespace App\Http\Controllers;

/**
 * 摸鱼小游戏控制器
 *
 * 提供与工作无关的休闲小游戏页面，用于上班间隙放松（摸鱼）
 *
 * @package App\Http\Controllers
 */
class GamesController extends Controller
{
    /**
     * 小游戏导航首页
     *
     * GET /games
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 小游戏清单（后续每新增一个游戏，在这里加一项即可自动出现在首页）
        $games = [
            [
                'key'     => 'woodfish',
                'name'    => '敲木鱼',
                'desc'    => '电子木鱼，一键敲出功德，佛系摸鱼首选',
                'emoji'   => '🪵',
                'color'   => 'gold',
                'url'     => '/games/woodfish',
                'ready'   => true,
            ],
            [
                'key'     => 'whack',
                'name'    => '打地鼠',
                'desc'    => '疯狂点击冒头地鼠，释放工作压力',
                'emoji'   => '🐹',
                'color'   => 'green',
                'url'     => '/games/whack',
                'ready'   => true,
            ],
            [
                'key'     => 'clock',
                'name'    => '错位时钟',
                'desc'    => '把错位的三根指针全部校准回 12 点',
                'emoji'   => '🕐',
                'color'   => 'blue',
                'url'     => '/games/clock',
                'ready'   => true,
            ],
            [
                'key'     => 'prism',
                'name'    => '光影迷阵',
                'desc'    => '用棱镜搭建光路，在激光迷阵中求生',
                'emoji'   => '✨',
                'color'   => 'purple',
                'url'     => '/games/prism',
                'ready'   => true,
            ],
            [
                'key'     => '2048',
                'name'    => '2048',
                'desc'    => '滑动合并数字方块，冲击 2048 高分',
                'emoji'   => '🧩',
                'color'   => 'cyan',
                'url'     => '/games/2048',
                'ready'   => true,
            ],
        ];

        return view('games.index', [
            'games' => $games,
        ]);
    }

    /**
     * 敲木鱼小游戏
     *
     * GET /games/woodfish
     *
     * @return \Illuminate\View\View
     */
    public function woodfish()
    {
        return view('games.woodfish');
    }

    /**
     * 打地鼠小游戏
     *
     * GET /games/whack
     *
     * @return \Illuminate\View\View
     */
    public function whack()
    {
        return view('games.whack');
    }

    /**
     * 错位时钟小游戏
     *
     * GET /games/clock
     *
     * @return \Illuminate\View\View
     */
    public function clock()
    {
        return view('games.clock');
    }

    /**
     * 光影迷阵小游戏
     *
     * GET /games/prism
     *
     * @return \Illuminate\View\View
     */
    public function prism()
    {
        return view('games.prism');
    }

    /**
     * 2048 小游戏
     *
     * GET /games/2048
     *
     * @return \Illuminate\View\View
     */
    public function game2048()
    {
        return view('games.game2048');
    }
}
