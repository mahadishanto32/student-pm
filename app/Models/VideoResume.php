<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoResume extends Model
{
    use HasFactory;

    protected $table = 'video_resumes';

    protected $fillable = [
        'project_id',
        'title',
        'media_file',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
