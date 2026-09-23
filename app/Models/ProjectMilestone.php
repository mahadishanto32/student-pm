<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    use HasFactory;

    protected $table = 'project_milestones';

    protected $fillable = [
        'project_id',
        'title',
        'tentative_time',
        'supervisor_note',
        'status',
        'done_by',
    ];

    protected $casts = [
        'tentative_time' => 'datetime',
    ];

    // Status constants (optional but recommended)
    const STATUS_PENDING         = 'pending';
    const STATUS_NEED_CORRECTION = 'need_correction';
    const STATUS_COMPLETED       = 'completed';
    const STATUS_REJECTED        = 'rejected';

    /* ---------- Relationships ---------- */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function doneBy()
    {
        return $this->belongsTo(User::class, 'done_by');
    }

    public function tasks()
    {
        return $this->hasMany(MilestoneTask::class, 'project_milestone_id');
    }
}