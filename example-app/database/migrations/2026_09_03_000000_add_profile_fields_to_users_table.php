<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // 摸鱼专属昵称（展示用，可与登录邮箱区分）
            $table->string('nickname')->nullable()->after('name');
            // 摸鱼宣言 / 个性签名
            $table->string('signature', 200)->nullable()->after('email');
            // 记录最近一次登录时间，用于首页展示
            $table->timestamp('last_login_at')->nullable()->after('signature');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nickname', 'signature', 'last_login_at']);
        });
    }
}
