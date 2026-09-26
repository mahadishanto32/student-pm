<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'done_by',
        'key_points',
        'supervisor_feedback',
        'date_of_presentation',
        'marks',
        'presentation_file',
        'status',
    ];

    protected $casts = [
        'date_of_presentation' => 'date',
        'marks'                => 'decimal:2',
    ];

    /**
     * The project this presentation belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The user who gave the presentation.
     */
    public function presenter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by');
    }

    /**
     * Scope: filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if the presentation is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}