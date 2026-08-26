<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * 测试接口控制器
 *
 * 用于外部访问连通性验证
 *
 * @package App\Http\Controllers
 */
class TestController extends Controller
{
    /**
     * 测试接口
     *
     * GET /api/test
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return response()->json([
            'code'    => 0,
            'message' => 'success',
            'data'    => [
                'service'  => 'test',
                'time'     => now()->toDateTimeString(),
                'message'  => '接口连通正常',
            ],
        ]);
    }
}
