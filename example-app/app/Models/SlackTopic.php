<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 群聊"摸鱼话题"模型。
 *
 * 每个话题对应某一天（topic_date），可包含标题与背景说明；
 * 群里所有人都可以在该话题下发表自己的意见（见 comments）。
 *
 * @package App\Models
 */
class SlackTopic extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'topic_date',
        'title',
        'description',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'topic_date' => 'date',
    ];

    /**
     * 发起话题的成员
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 该话题下所有成员的发言（按时间正序展示讨论脉络）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(SlackTopicComment::class)->orderBy('created_at');
    }
}
