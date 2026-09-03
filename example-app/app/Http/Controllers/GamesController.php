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
     * 游戏大厅首页
     *
     * GET /games
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('games.index');
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
}
