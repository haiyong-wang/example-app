<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PhoneReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\SlackController;
use App\Http\Controllers\SlackTopicController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|--------------------------------------------------------------------------
| 认证：登录 / 注册 / 退出
|--------------------------------------------------------------------------
*/

// 注册表单
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
// 提交注册
Route::post('/register', [AuthController::class, 'register']);

// 登录表单
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
// 提交登录
Route::post('/login', [AuthController::class, 'login']);

// 退出登录
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| 需要登录才能访问的内容区（憨憨专属）
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // 首页 / 个人面板
    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    /*
    |--------------------------------------------------------------------------
    | 报表页面
    |--------------------------------------------------------------------------
    */

    // 机型筛选日报(页面骨架)
    Route::get('/reports/daily', [PhoneReportController::class, 'daily']);

    // 机型筛选日报数据(异步 JSON 接口)
    Route::get('/reports/daily/data', [PhoneReportController::class, 'dailyData']);

    /*
    |--------------------------------------------------------------------------
    | 产品中心
    |--------------------------------------------------------------------------
    */

    // 产品中心(默认手机产品)
    Route::get('/products', [ProductController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | 实用工具
    |--------------------------------------------------------------------------
    */

    // 实用工具导航首页
    Route::get('/tools', [ToolsController::class, 'index']);

    // 二维码生成
    Route::get('/tools/qrcode', [ToolsController::class, 'qrcode']);

    // 八字算命（GET 展示表单 / POST 提交测算）
    Route::match(['get', 'post'], '/tools/fortune', [ToolsController::class, 'fortune']);

    /*
    |--------------------------------------------------------------------------
    | 摸鱼游戏
    |--------------------------------------------------------------------------
    */

    // 小游戏导航首页
    Route::get('/games', [GamesController::class, 'index']);

    // 敲木鱼
    Route::get('/games/woodfish', [GamesController::class, 'woodfish']);

    // 打地鼠
    Route::get('/games/whack', [GamesController::class, 'whack']);

    // 错位时钟
    Route::get('/games/clock', [GamesController::class, 'clock']);

    // 光影迷阵
    Route::get('/games/prism', [GamesController::class, 'prism']);

    // 2048
    Route::get('/games/2048', [GamesController::class, 'game2048']);

    /*
    |--------------------------------------------------------------------------
    | 摸鱼时长统计（自动记录在线时长）
    |--------------------------------------------------------------------------
    */

    // 摸鱼统计页面
    Route::get('/slack', [SlackController::class, 'index'])->name('slack.index');

    // 心跳接口：前端周期性调用，自动开启/刷新/结算在线会话
    Route::post('/slack/heartbeat', [SlackController::class, 'heartbeat'])->name('slack.heartbeat');

    // 实时摸鱼时长(异步 JSON，页面轮询校准用)
    Route::get('/slack/status', [SlackController::class, 'status'])->name('slack.status');

    /*
    |--------------------------------------------------------------------------
    | 群聊摸鱼话题导航
    |--------------------------------------------------------------------------
    */

    // 独立的话题列表页：浏览话题并在这里发起新话题（原 /slack-topics 新增页已下线）
    Route::get('/slack-topics/list', [SlackTopicController::class, 'list'])->name('slack-topics.list');

    // 新建话题表单页：填写标题、日期、背景说明后提交创建
    Route::get('/slack-topics/create', [SlackTopicController::class, 'create'])->name('slack-topics.create');

    // 新增每日摸鱼话题
    Route::post('/slack-topics', [SlackTopicController::class, 'store'])->name('slack-topics.store');

    // 话题详情：查看讨论的具体内容、谁发表了什么意见
    Route::get('/slack-topics/{slackTopic}', [SlackTopicController::class, 'show'])->name('slack-topics.show');

    // 在话题下发表意见
    Route::post('/slack-topics/{slackTopic}/comments', [SlackTopicController::class, 'storeComment'])->name('slack-topics.comments.store');
});
