<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectMark extends Model
{
    use HasFactory;

    protected $table = 'project_marks';

    /** Every topic is marked out of this value (5 topics x 20 = 100) */
    public const MARKS_PER_MILESTONE = 20;
    public const MARKS_PER_TOPIC = self::MARKS_PER_MILESTONE; 

    /** Highest threshold first */
    public const GRADING_SCALE = [
        ['min' => 80, 'range' => '80% and above', 'grade' => 'A+', 'point' => '4'],
        ['min' => 75, 'range' => '75% to <80%',   'grade' => 'A',  'point' => '3.75'],
        ['min' => 70, 'range' => '70% to <75%',   'grade' => 'A-', 'point' => '3.5'],
        ['min' => 65, 'range' => '65% to <70%',   'grade' => 'B+', 'point' => '3.25'],
        ['min' => 60, 'range' => '60% to <65%',   'grade' => 'B',  'point' => '3'],
        ['min' => 55, 'range' => '55% to <60%',   'grade' => 'B-', 'point' => '2.75'],
        ['min' => 50, 'range' => '50% to <55%',   'grade' => 'C+', 'point' => '2.5'],
        ['min' => 45, 'range' => '45% to <50%',   'grade' => 'C',  'point' => '2.25'],
        ['min' => 40, 'range' => '40% to <45%',   'grade' => 'D',  'point' => '2'],
        ['min' => 0,  'range' => 'Less than 40%', 'grade' => 'F',  'point' => '0'],
    ];

    protected $fillable = [
        'project_id',
        'supervisor_id',
        'student_id',
        'remarks',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(MarksDistribution::class, 'project_marks_id');
    }

    /* ---------- Marks & grading helpers ---------- */

    public function getIsCompleteAttribute(): bool
    {
        return $this->distributions->count() === count(MarksDistribution::TOPICS);
    }

    public function getTotalGivenAttribute(): float
    {
        return (float) $this->distributions->sum('given_marks');
    }

    public function getTotalOutOfAttribute(): float
    {
        return (float) $this->distributions->sum('out_of');
    }

    public function getPercentageAttribute(): float
    {
        return round(($this->total_given / MarksDistribution::TOTAL_MARKS) * 100, 2);
    }

    /**
     * @return array{grade:string, point:string}
     */
    public function getGradeInfoAttribute(): array
    {
        // Missing topic marks => Incomplete
        if (! $this->is_complete) {
            return ['grade' => 'I', 'point' => '-'];
        }

        foreach (self::GRADING_SCALE as $row) {
            if ($this->percentage >= $row['min']) {
                return ['grade' => $row['grade'], 'point' => $row['point']];
            }
        }

        return ['grade' => 'F', 'point' => '0'];
    }

    /**
     * Recalculate the "Milestones" topic (out of 100) from all COMPLETED
     * milestones of this project for this student.
     */
    public static function syncMilestoneScore(int $projectId, int $studentId): void
    {
        $totals = MilestoneMark::query()
            ->where('student_id', $studentId)
            ->whereHas('milestone', fn ($q) => $q
                ->where('project_id', $projectId)
                ->where('status', ProjectMilestone::STATUS_COMPLETED))
            ->selectRaw('SUM(given_marks) AS given, SUM(out_of) AS out_of')
            ->first();

        $given = (float) ($totals->given ?? 0);
        $outOf = (float) ($totals->out_of ?? 0);

        $pm = static::where('project_id', $projectId)->where('student_id', $studentId)->first();

        if ($outOf <= 0) {
            if ($pm) {
                DB::table('marks_distributions')
                    ->where('project_marks_id', $pm->id)->where('topic', 'Milestones')->delete();
            }
            return;
        }

        $pm ??= static::create(['project_id' => $projectId, 'student_id' => $studentId]);
        $pm->supervisor_id = Auth::id();
        $pm->save();

        $score = round(($given / $outOf) * 100, 2);
        $now   = now();
        $query = DB::table('marks_distributions')
            ->where('project_marks_id', $pm->id)->where('topic', 'Milestones');

        $values = [
            'given_marks' => $score,
            'out_of'      => MarksDistribution::TOPIC_MAX['Milestones'],
            'updated_at'  => $now,
        ];

        if ($query->exists()) {
            $query->update($values);
        } else {
            DB::table('marks_distributions')->insert($values + [
                'project_marks_id' => $pm->id,
                'topic'            => 'Milestones',
                'created_at'       => $now,
            ]);
        }
    }
}
