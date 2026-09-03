<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 群聊摸鱼话题下的"成员意见 / 发言"模型。
 *
 * @package App\Models
 */
class SlackTopicComment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slack_topic_id',
        'user_id',
        'content',
    ];

    /**
     * 所属话题
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function topic()
    {
        return $this->belongsTo(SlackTopic::class, 'slack_topic_id');
    }

    /**
     * 发言成员
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
