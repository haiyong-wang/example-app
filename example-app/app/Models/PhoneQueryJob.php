<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 待查询手机号任务源模型
 *
 * @package App\Models
 */
class PhoneQueryJob extends Model
{
    /**
     * 状态常量
     */
    const STATUS_PENDING  = 0;  // 待处理
    const STATUS_PROCESSING = 1; // 处理中
    const STATUS_SUCCESS  = 2;  // 处理成功
    const STATUS_FAILED   = 3;  // 处理失败

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'phone_query_jobs';

    /**
     * 可批量赋值字段
     *
     * @var array
     */
    protected $fillable = [
        'batch_no',
        'phone',
        'target_brands',
        'phone_type',
        'source',
        'status',
        'remark',
        'query_id',
        'model_name',
        'matched',
        'scheduled_at',
        'processed_at',
    ];

    /**
     * 日期字段
     *
     * @var array
     */
    protected $dates = [
        'scheduled_at',
        'processed_at',
    ];

    /**
     * 获取目标品牌ID数组
     *
     * @return array
     */
    public function getTargetBrandsArray()
    {
        return array_values(array_filter(array_map('intval', explode(',', (string) $this->target_brands))));
    }

    /**
     * 设置目标品牌(数组转字符串)
     *
     * @param array $brands
     * @return void
     */
    public function setTargetBrands(array $brands)
    {
        $this->target_brands = implode(',', array_values(array_unique(array_map('intval', $brands))));
    }
}
