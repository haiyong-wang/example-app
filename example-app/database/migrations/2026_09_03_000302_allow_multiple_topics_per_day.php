<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 允许同一天创建多个摸鱼话题。
 *
 * 将 slack_topics.topic_date 从唯一字段改为普通字段，
 * 之后同一天可以创建任意多个话题。
 */
class AllowMultipleTopicsPerDay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slack_topics', function (Blueprint $table) {
            // 去掉 topic_date 的唯一索引
            $table->dropUnique('slack_topics_topic_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slack_topics', function (Blueprint $table) {
            $table->unique('topic_date');
        });
    }
}
