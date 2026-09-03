<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlackSessionsTable extends Migration
{
    /**
     * 记录用户的每一次"摸鱼"会话。
     *
     * started_at 为空表示……不会为空；ended_at 为空表示该次摸鱼仍在进行中，
     * 通过 ended_at - started_at 累加即可得到某一天的摸鱼时长。
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slack_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // 本次摸鱼开始时间
            $table->timestamp('started_at');
            // 本次摸鱼结束时间（为 null 代表正在进行中）
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            // 便于查询"某个用户某一天"的摸鱼记录，以及统计进行中的会话
            $table->index(['user_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slack_sessions');
    }
}
