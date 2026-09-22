<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'group_number',
        'project_name',
        'project_topic',
        'short_overview',
        'assigned_teacher',
        'start_date',
        'tentative_end_date',
        'status',
    ];

    protected $casts = [
        'start_date'         => 'date',
        'tentative_end_date' => 'date',
    ];

    /**
     * Assigned teacher (single user).
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_teacher');
    }

    /**
     * Assigned team members (many-to-many with users).
     */
    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id')
                ->withTimestamps();
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function projectBook()
    {
        return $this->hasOne(ProjectBook::class);
    }
}