<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarksDistribution extends Model
{
    use HasFactory;

    protected $table = 'marks_distributions';

    // Composite primary key: Eloquent doesn't support it natively
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'project_marks_id',
        'topic',
        'given_marks',
        'out_of',
    ];

    protected $casts = [
        'given_marks' => 'decimal:2',
        'out_of'      => 'decimal:2',
    ];

    public const TOPICS = ['Milestones', 'Project Book', 'Presentation'];

    /** Max marks per topic. Total = 200 */
    public const TOPIC_MAX = [
        'Milestones'   => 100, // teacher: average of milestone marks, scaled to 100
        'Project Book' => 40,  // not editable by the teacher
        'Presentation' => 60,  // not editable by the teacher
    ];

    public const TOTAL_MARKS = 200;

    public function projectMark(): BelongsTo
    {
        return $this->belongsTo(ProjectMark::class, 'project_marks_id');
    }
}
