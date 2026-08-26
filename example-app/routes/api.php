<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhoneModelController;
use App\Http\Controllers\PhoneQueryJobController;
use App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| 手机型号查询接口
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'phone-model'], function () {
    // 本地查询接口: 接收查询参数, 调用第三方, 结果入库
    // POST /api/phone-model/query/{id}/{cid}/{code}/{type}
    Route::post('query/{id?}/{cid?}/{code?}/{type?}', [PhoneModelController::class, 'query']);

    // 查询记录详情(含结果明细)
    // GET /api/phone-model/query/{id}
    Route::get('query/{id}', [PhoneModelController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| 测试接口
|--------------------------------------------------------------------------
*/

// 连通性测试: GET /api/test
Route::get('test', [TestController::class, 'index']);

/*
|--------------------------------------------------------------------------
| 待查询手机号任务源管理接口
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'phone-query-jobs'], function () {
    // 批量添加待查询手机号任务(供定时任务消费)
    // POST /api/phone-query-jobs
    Route::post('/', [PhoneQueryJobController::class, 'store']);

    // 待处理任务列表
    // GET /api/phone-query-jobs?status=0&limit=20
    Route::get('/', [PhoneQueryJobController::class, 'index']);
});
