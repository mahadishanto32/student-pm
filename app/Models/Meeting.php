<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'meeting_date_and_time',
        'title',
        'description',
        'supervisor_note',
        'platform',
        'type',
        'tentative_next_meeting_date_and_time',
    ];

    protected $casts = [
        'meeting_date_and_time' => 'datetime',
        'tentative_next_meeting_date_and_time' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
