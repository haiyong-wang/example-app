<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlackTopicCommentsTable extends Migration
{
    /**
     * 群聊摸鱼话题下的"成员意见 / 发言"记录表。
     *
     * 记录每条发言属于哪个话题、是谁发的、说了什么内容，
     * 用于话题详情页展示"具体讨论了什么、谁发表了什么意见"。
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slack_topic_comments', function (Blueprint $table) {
            $table->id();
            // 所属话题
            $table->foreignId('slack_topic_id')->constrained()->onDelete('cascade');
            // 发言成员
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // 发言内容
            $table->text('content');
            $table->timestamps();

            // 便于查询"某个话题下的所有发言"
            $table->index(['slack_topic_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slack_topic_comments');
    }
}
