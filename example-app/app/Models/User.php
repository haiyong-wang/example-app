<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'nickname',
        'email',
        'password',
        'signature',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
    ];

    /**
     * 该用户的所有摸鱼会话（按开始时间倒序）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function slackSessions()
    {
        return $this->hasMany(SlackSession::class);
    }

    /**
     * 获取当前正在进行中（尚未结算）的摸鱼会话（无则返回 null）
     *
     * @return \App\Models\SlackSession|null
     */
    public function activeSlackSession()
    {
        return $this->slackSessions()
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
    }
}
