<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastBeatAtToSlackSessionsTable extends Migration
{
    /**
     * 为"自动记录在线时长"增加"最后活跃时间"字段。
     *
     * 前端会周期性发送心跳，后端通过 last_beat_at 判断当前会话是否仍然活跃：
     * 若距离上次心跳超过"离线阈值"，则认为之前那次会话已结束，需要自动结算并开启新会话。
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slack_sessions', function (Blueprint $table) {
            $table->timestamp('last_beat_at')->nullable()->after('ended_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slack_sessions', function (Blueprint $table) {
            $table->dropColumn(['last_beat_at']);
        });
    }
}
