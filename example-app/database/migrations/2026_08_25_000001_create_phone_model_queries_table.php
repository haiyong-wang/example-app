<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhoneModelQueriesTable extends Migration
{
    /**
     * 手机型号查询记录表
     *
     * 记录每次调用第三方接口的请求与响应信息
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_model_queries', function (Blueprint $table) {
            $table->bigIncrements('id');
            // 本地调用方标识(用于区分业务来源, 可选)
            $table->string('source', 50)->nullable()->index();
            // 第三方接口路径参数
            $table->string('api_id', 50)->nullable();
            $table->string('api_cid', 50)->nullable();
            $table->string('api_code', 100)->nullable();
            $table->integer('api_type')->default(10001);
            // 请求参数
            $table->integer('phone_count')->default(0)->comment('请求手机号数量');
            $table->string('phone_type', 20)->default('MD5')->comment('PLAINTEXT明文 / MD5');
            $table->text('request_params')->nullable()->comment('完整请求参数(JSON)');
            // 响应信息
            $table->integer('response_code')->nullable()->comment('第三方返回的业务码');
            $table->string('response_message', 255)->nullable()->comment('第三方返回的信息');
            $table->longText('response_data')->nullable()->comment('第三方返回原始数据(JSON)');
            // 状态: 1=成功, 0=失败, 2=超时
            $table->tinyInteger('status')->default(0)->index();
            $table->string('error_message', 500)->nullable();
            // 耗时
            $table->decimal('duration_ms', 12, 2)->nullable()->comment('接口耗时(毫秒)');
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('phone_model_queries');
    }
}
