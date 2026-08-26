<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhoneReportController;
use App\Http\Controllers\ProductController;

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

// 机型筛选日报
Route::get('/reports/daily', [PhoneReportController::class, 'daily']);

/*
|--------------------------------------------------------------------------
| 产品中心
|--------------------------------------------------------------------------
*/

// 产品中心(默认手机产品)
Route::get('/products', [ProductController::class, 'index']);
