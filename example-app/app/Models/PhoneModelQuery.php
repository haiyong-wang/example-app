<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 手机型号查询记录模型
 *
 * @package App\Models
 */
class PhoneModelQuery extends Model
{
    /**
     * 状态常量
     */
    const STATUS_SUCCESS = 1;   // 成功
    const STATUS_FAILED  = 0;   // 失败
    const STATUS_TIMEOUT = 2;   // 超时

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'phone_model_queries';

    /**
     * 可批量赋值字段
     *
     * @var array
     */
    protected $fillable = [
        'source',
        'api_id',
        'api_cid',
        'api_code',
        'api_type',
        'phone_count',
        'phone_type',
        'request_params',
        'response_code',
        'response_message',
        'response_data',
        'status',
        'error_message',
        'duration_ms',
    ];

    /**
     * 关联查询结果明细
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function results()
    {
        return $this->hasMany(PhoneModelResult::class, 'query_id');
    }
}
