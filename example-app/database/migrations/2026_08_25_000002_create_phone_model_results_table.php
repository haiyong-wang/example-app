<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhoneModelResultsTable extends Migration
{
    /**
     * 手机型号查询结果明细表
     *
     * 记录每个手机号匹配到的机型结果
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_model_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            // 关联查询记录
            $table->unsignedBigInteger('query_id')->index();
            // 手机号(原文或md5)
            $table->string('phone', 64)->index();
            // 匹配到的目标机型名称
            $table->string('model_name', 100)->nullable()->comment('匹配到的机型');
            // 是否命中目标机型: 1=命中, 0=未命中
            $table->tinyInteger('matched')->default(0)->index();
            // 原始返回的机型结果(如多个型号, JSON数组)
            $table->string('brands', 500)->nullable();
            // 结果明细原始数据
            $table->longText('raw_result')->nullable();
            $table->timestamps();

            $table->foreign('query_id')
                ->references('id')
                ->on('phone_model_queries')
                ->onDelete('cascade');

            $table->index(['query_id', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('phone_model_results');
    }
}
