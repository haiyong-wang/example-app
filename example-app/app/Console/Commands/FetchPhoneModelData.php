<?php

namespace App\Console\Commands;

use App\Models\PhoneQueryJob;
use App\Services\PhoneModelService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * 定时拉取手机型号查询接口数据
 *
 * 从 phone_query_jobs 源表中获取待处理手机号, 批量调用第三方接口,
 * 并将结果(命中机型/品牌)回写到源表及查询结果表中
 *
 * 调度: 每小时执行一次
 *
 * @package App\Console\Commands
 */
class FetchPhoneModelData extends Command
{
    /**
     * 命令签名
     *
     * @var string
     */
    protected $signature = 'phonemodel:fetch
                            {--limit=5000 : 本次最多处理的手机号数量}
                            {--batch=500 : 每次调用接口的手机号批次大小}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '定时拉取手机型号查询接口数据并保存到数据库';

    /**
     * @var PhoneModelService
     */
    protected $service;

    public function __construct(PhoneModelService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $batchSize = (int) $this->option('batch');
        $maxBatch = (int) config('phonemodel.max_phones', 10000);
        if ($batchSize > $maxBatch) {
            $batchSize = $maxBatch;
        }

        $this->info('开始拉取手机型号查询接口数据...');

        // 取出待处理的源记录
        $pending = PhoneQueryJob::where('status', PhoneQueryJob::STATUS_PENDING)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $total = $pending->count();
        if ($total === 0) {
            $this->info('没有待处理的手机号数据。');
            return 0;
        }

        $this->info("共 {$total} 条待处理记录, 按每批 {$batchSize} 条分批处理...");

        // 按批次大小分组
        $chunks = $pending->chunk($batchSize);

        $successCount = 0;
        $failCount = 0;

        foreach ($chunks as $chunk) {
            $result = $this->processBatch($chunk);
            $successCount += $result['success'];
            $failCount += $result['failed'];
        }

        $this->info("处理完成: 成功 {$successCount} 条, 失败 {$failCount} 条。");
        return 0;
    }

    /**
     * 处理一批数据
     *
     * @param \Illuminate\Support\Collection $chunk
     * @return array [success, failed]
     */
    protected function processBatch($chunk)
    {
        $jobs = $chunk->all();
        if (empty($jobs)) {
            return ['success' => 0, 'failed' => 0];
        }

        // 汇总本批的手机号与目标品牌
        $phones = [];
        $brands = [];
        foreach ($jobs as $job) {
            $phones[] = $job->phone;
            $jobBrands = $job->getTargetBrandsArray();
            foreach ($jobBrands as $b) {
                $brands[$b] = $b;
            }
        }
        $phones = array_values(array_unique($phones));
        $targetBrands = array_values(array_unique($brands));

        // 标记本批为处理中
        $jobIds = array_column($jobs, 'id');

        $phoneType = $jobs[0]->phone_type ?: 'PLAINTEXT';
        $source = $jobs[0]->source;

        // 处理中的手机号数量(去重后的)
        $this->info("调用第三方接口, 手机号 " . count($phones) . " 个, 目标品牌 " . implode(',', $targetBrands) . "...");

        try {
            $result = $this->service->query($phones, $targetBrands, $phoneType, $source);
        } catch (\Exception $e) {
            $this->error('接口调用异常: ' . $e->getMessage());
            Log::error('定时拉取手机型号接口异常', ['error' => $e->getMessage()]);

            // 全部标记失败
            PhoneQueryJob::whereIn('id', $jobIds)->update([
                'status'       => PhoneQueryJob::STATUS_FAILED,
                'remark'       => '接口调用异常: ' . $e->getMessage(),
                'processed_at' => now(),
            ]);
            return ['success' => 0, 'failed' => count($jobIds)];
        }

        $query = $result['query'];
        $queryId = $query->id;
        $details = isset($result['details']) ? $result['details'] : [];
        $isSuccess = $result['status'];

        // 逐个更新源表记录
        foreach ($jobs as $job) {
            $jobUpdate = [
                'query_id'     => $queryId,
                'processed_at' => now(),
            ];

            if ($isSuccess) {
                $detail = isset($details[$job->phone]) ? $details[$job->phone] : null;
                if ($detail) {
                    $jobUpdate['status'] = PhoneQueryJob::STATUS_SUCCESS;
                    $jobUpdate['model_name'] = $detail['model_name'];
                    $jobUpdate['matched'] = $detail['matched'] ? 1 : 0;
                    $jobUpdate['remark'] = '查询成功';
                } else {
                    // 接口成功但未返回该手机号, 视为未命中
                    $jobUpdate['status'] = PhoneQueryJob::STATUS_SUCCESS;
                    $jobUpdate['matched'] = 0;
                    $jobUpdate['remark'] = '查询成功(未返回明细)';
                }
            } else {
                $jobUpdate['status'] = PhoneQueryJob::STATUS_FAILED;
                $jobUpdate['remark'] = '查询失败: ' . ($query->error_message ?: $query->response_message);
            }

            $job->update($jobUpdate);
        }

        $success = PhoneQueryJob::whereIn('id', $jobIds)
            ->where('status', PhoneQueryJob::STATUS_SUCCESS)
            ->count();
        $failed = count($jobIds) - $success;

        return ['success' => $success, 'failed' => $failed];
    }
}
