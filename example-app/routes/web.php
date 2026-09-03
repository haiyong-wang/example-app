<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhoneReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\GamesController;

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

Route::get('/', function () {
    return view('welcome');
});

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

/*
|--------------------------------------------------------------------------
| 摸鱼游戏
|--------------------------------------------------------------------------
*/

// 敲木鱼
Route::get('/games/woodfish', [GamesController::class, 'woodfish']);

// 打地鼠
Route::get('/games/whack', [GamesController::class, 'whack']);

// 错位时钟
Route::get('/games/clock', [GamesController::class, 'clock']);

// 光影迷阵
Route::get('/games/prism', [GamesController::class, 'prism']);
