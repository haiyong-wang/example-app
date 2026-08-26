<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 手机型号查询结果明细模型
 *
 * @package App\Models
 */
class PhoneModelResult extends Model
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'phone_model_results';

    /**
     * 可批量赋值字段
     *
     * @var array
     */
    protected $fillable = [
        'query_id',
        'phone',
        'model_name',
        'matched',
        'brands',
        'raw_result',
    ];

    /**
     * 关联查询记录
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function queryRecord()
    {
        return $this->belongsTo(PhoneModelQuery::class, 'query_id');
    }
}
