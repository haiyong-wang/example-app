<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhoneQueryJobsTable extends Migration
{
    /**
     * 待查询手机号任务源表
     *
     * 记录需要定时调用第三方接口查询机型的手机号及其目标品牌
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_query_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            // 业务批次号/任务编号(可选)
            $table->string('batch_no', 50)->nullable()->index();
            // 手机号
            $table->string('phone', 32)->index();
            // 目标品牌ID(对应 targetBrands, 多个用逗号分隔)
            $table->string('target_brands', 200)->nullable();
            // 数据类型: PLAINTEXT / MD5
            $table->string('phone_type', 20)->default('PLAINTEXT');
            // 业务来源标识
            $table->string('source', 50)->nullable();
            // 处理状态: 0=待处理 1=处理中 2=处理成功 3=处理失败
            $table->tinyInteger('status')->default(0)->index();
            // 处理结果信息
            $table->string('remark', 500)->nullable();
            // 关联的查询记录ID
            $table->unsignedBigInteger('query_id')->nullable()->index();
            // 查询结果中匹配到的机型名称
            $table->string('model_name', 100)->nullable();
            // 是否命中目标品牌: 1=命中 0=未命中
            $table->tinyInteger('matched')->default(0)->index();
            // 计划处理时间(可用于分批/限流)
            $table->timestamp('scheduled_at')->nullable()->index();
            // 处理时间
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('phone_query_jobs');
    }
}
