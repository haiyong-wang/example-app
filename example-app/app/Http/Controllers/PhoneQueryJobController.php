<?php

namespace App\Http\Controllers;

use App\Models\PhoneQueryJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 待查询手机号任务源管理接口
 *
 * 业务方通过此接口提交需要定时查询的手机号
 *
 * @package App\Http\Controllers
 */
class PhoneQueryJobController extends Controller
{
    /**
     * 批量添加待查询手机号任务
     *
     * POST /api/phone-query-jobs
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phones'       => 'required|array|min:1',
            'phones.*'     => 'required|string|max:32',
            'targetBrands' => 'required|array|min:1',
            'targetBrands.*' => 'required|integer',
            'phoneType'    => 'sometimes|string|in:PLAINTEXT,MD5,plaintext,md5',
            'batchNo'      => 'sometimes|string|max:50',
            'source'       => 'sometimes|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 400,
                'message' => '参数校验失败',
                'errors'  => $validator->errors(),
                'data'    => null,
            ]);
        }

        $phones = array_values(array_unique(array_map('trim', $request->input('phones'))));
        $targetBrands = array_values(array_unique(array_map('intval', $request->input('targetBrands'))));
        $phoneType = strtoupper($request->input('phoneType', 'PLAINTEXT'));
        $batchNo = $request->input('batchNo');
        $source = $request->input('source');

        $rows = [];
        $now = now();
        foreach ($phones as $phone) {
            $rows[] = [
                'batch_no'      => $batchNo,
                'phone'         => $phone,
                'target_brands' => implode(',', $targetBrands),
                'phone_type'    => $phoneType,
                'source'        => $source,
                'status'        => PhoneQueryJob::STATUS_PENDING,
                'scheduled_at'  => $now,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        // 使用 chunk 避免一次插入过多
        foreach (array_chunk($rows, 500) as $chunk) {
            PhoneQueryJob::insert($chunk);
        }

        return response()->json([
            'code'    => 200,
            'message' => '添加成功, 等待定时任务处理',
            'data'    => [
                'added'   => count($rows),
                'phones'  => $phones,
                'targetBrands' => $targetBrands,
            ],
        ]);
    }

    /**
     * 查询待处理任务列表
     *
     * GET /api/phone-query-jobs?status=0&limit=20
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $limit = min((int) $request->input('limit', 20), 200);

        $query = PhoneQueryJob::query()->orderBy('id', 'desc');

        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        $items = $query->limit($limit)->get();

        return response()->json([
            'code'    => 200,
            'message' => 'ok',
            'data'    => $items,
        ]);
    }
}
