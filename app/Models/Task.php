<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use App\Models\Tag;

class Task extends Model
{
    use HasFactory, SoftDeletes;


    // ステータスの定数
    public const STATUS_NOT_STARTED = 'not_started';   // 未着手
    public const STATUS_IN_PROGRESS = 'in_progress';   // 進行中
    public const STATUS_COMPLETED   = 'completed';     // 完了

    /**
     * ステータスの選択肢（値 => ラベル）
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_NOT_STARTED => '未着手',
            self::STATUS_IN_PROGRESS => '進行中',
            self::STATUS_COMPLETED   => '完了',
        ];
    }

    /**
     * ステータスのラベルを取得するアクセサ
     *
     * $task->status_label で「未着手 / 進行中 / 完了」が取れる
     */
    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? '不明';
    }

    /**
     * 一括代入を許可するカラム
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'priority'     => 'integer',
        'due_date'     => 'date',
        'completed_at' => 'datetime',
        'status'       => 'string',
    ];

    /**
     * このタスクの担当ユーザー
     *
     * 多数タスク : 1ユーザー の「多対1」リレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * このタスクに紐づくタグ一覧
     *
     * 多対多リレーション (tasks - tags)
     */
    public function tags(): BelongsToMany
    {
        // 第2引数はテーブル名。'tag_task' で作ってるので明示しておく。
        return $this->belongsToMany(Tag::class, 'tag_task')->withTimestamps();
    }
}
