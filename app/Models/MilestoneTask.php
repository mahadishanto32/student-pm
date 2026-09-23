<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilestoneTask extends Model
{
    use HasFactory;

    protected $table = 'milestone_tasks';

    protected $fillable = [
        'project_milestone_id',
        'key_points',
        'document',
    ];

    /* ---------- Relationships ---------- */

    public function projectMilestone()
    {
        return $this->belongsTo(ProjectMilestone::class, 'project_milestone_id');
    }
}