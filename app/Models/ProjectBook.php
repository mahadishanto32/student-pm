<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBook extends Model
{
    use HasFactory;

    protected $table = 'project_books';

    protected $fillable = [
        'project_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_APPROVED  = 'approved';
    public const STATUS_PENDING   = 'pending';
    public const STATUS_WORKING   = 'working';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public static function statuses(): array
    {
        return [
            self::STATUS_APPROVED,
            self::STATUS_PENDING,
            self::STATUS_WORKING,
            self::STATUS_COMPLETED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(ProjectBookChapter::class)->orderBy('chapter_no');
    }
}