<?php

namespace App\Http\Controllers;

use App\Models\PhoneModelQuery;
use App\Services\PhoneModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 手机型号查询接口控制器
 *
 * 提供本地接口: 接收查询参数 -> 调用第三方 -> 结果入库 -> 返回
 *
 * @package App\Http\Controllers
 */
class PhoneModelController extends Controller
{
    /**
     * @var PhoneModelService
     */
    protected $service;

    public function __construct(PhoneModelService $service)
    {
        $this->service = $service;
    }

    /**
     * 本地查询接口
     *
     * POST /api/phone-model/query/{id}/{cid}/{code}/{type}
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(Request $request, $id = null, $cid = null, $code = null, $type = null)
    {
        // 校验参数
        $validator = Validator::make($request->all(), [
            'phones'       => 'required|array|min:1',
            'phones.*'     => 'required|string',
            'targetBrands' => 'required|array|min:1',
            'targetBrands.*' => 'required|integer',
            'phoneType'    => 'sometimes|string|in:PLAINTEXT,MD5,plaintext,md5',
            'source'       => 'sometimes|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 400,
                'message' => '参数校验失败',
                'errors'  => $validator->errors(),
                'data'    => null,
            ], 200);
        }

        $phones = $request->input('phones');
        $targetBrands = $request->input('targetBrands');
        $phoneType = $request->input('phoneType', 'MD5');
        $source = $request->input('source');

        // 校验手机号数量
        if (count($phones) > (int) config('phonemodel.max_phones', 10000)) {
            return response()->json([
                'code'    => 400,
                'message' => '手机号数量不能超过 ' . config('phonemodel.max_phones') . ' 个',
                'data'    => null,
            ]);
        }

        // 构造第三方路径参数覆盖(默认取配置文件)
        $pathParams = [];
        foreach (['id', 'cid', 'code', 'type'] as $key) {
            if (!is_null(${$key})) {
                $pathParams[$key] = ${$key};
            }
        }

        try {
            $result = $this->service->query($phones, $targetBrands, $phoneType, $source, $pathParams);

            $responseCode = $result['query']->response_code;
            $success = $result['status'];

            return response()->json([
                'code'    => $success ? ($responseCode ?? 200) : 500,
                'message' => $success
                    ? ($result['query']->response_message ?? '查询成功')
                    : ($result['query']->error_message ?? '查询失败'),
                'query_id' => $result['query']->id,
                'data'    => $result['data'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'code'    => 400,
                'message' => $e->getMessage(),
                'data'    => null,
            ]);
        }
    }

    /**
     * 查询记录详情(含结果明细)
     *
     * GET /api/phone-model/query/{id}
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $record = PhoneModelQuery::with('results')->find($id);

        if (!$record) {
            return response()->json([
                'code'    => 404,
                'message' => '记录不存在',
                'data'    => null,
            ]);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'ok',
            'data'    => $record,
        ]);
    }
}
