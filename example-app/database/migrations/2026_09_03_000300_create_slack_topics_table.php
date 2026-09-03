<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlackTopicsTable extends Migration
{
    /**
     * 群聊"摸鱼话题"记录表。
     *
     * 每个话题对应某一天（topic_date 唯一），包含标题与背景说明；
     * 群聊里的所有成员都可以在同一话题下发表各自的意见（见 slack_topic_comments）。
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slack_topics', function (Blueprint $table) {
            $table->id();
            // 话题归属的日期（每日一个话题，天然唯一）
            $table->date('topic_date')->unique();
            // 话题标题
            $table->string('title');
            // 背景说明 / 问题描述
            $table->text('description')->nullable();
            // 谁发起的这个话题
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
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
        Schema::dropIfExists('slack_topics');
    }
}
