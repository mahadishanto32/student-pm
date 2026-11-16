<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilestoneMark extends Model
{
    protected $fillable = ['project_milestone_id', 'student_id', 'given_marks', 'out_of', 'remarks'];

    protected $casts = ['given_marks' => 'decimal:2', 'out_of' => 'decimal:2'];

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'project_milestone_id');
    }
}