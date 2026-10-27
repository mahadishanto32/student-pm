<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ProjectMark;
use Illuminate\Support\Collection;

trait BuildsProjectMarkSheets
{
    /** form key => marks_distributions.topic */
    protected function topicMap(): array
    {
        return [
            'milestones'   => 'Milestones',
            'project_book' => 'Project Book',
            'presentation' => 'Presentation',
        ];
    }

    /**
     * Build one mark sheet per project.
     *
     * @param  Collection $projects        projects with teamMembers loaded
     * @param  int|null   $onlyStudentId   restrict to one student (student panel)
     * @return array<int, Collection>      project_id => rows
     */
    protected function buildSheets(Collection $projects, ?int $onlyStudentId = null): array
    {
        $topics   = array_values($this->topicMap());
        $perTopic = ProjectMark::MARKS_PER_TOPIC;
        $outOf    = $perTopic * count($topics);

        $marks = ProjectMark::with(['distributions', 'supervisor'])
            ->whereIn('project_id', $projects->pluck('id'))
            ->when($onlyStudentId, fn ($q) => $q->where('student_id', $onlyStudentId))
            ->get()
            ->groupBy('project_id');

        $sheets = [];

        foreach ($projects as $project) {
            $students = $onlyStudentId
                ? $project->teamMembers->where('id', $onlyStudentId)
                : $project->teamMembers->where('role', 'student');

            $byStudent = ($marks[$project->id] ?? collect())->keyBy('student_id');
            $rows      = collect();

            foreach ($students->values() as $student) {
                $pm    = $byStudent[$student->id] ?? null;
                $given = [];
                $total = 0.0;
                $count = 0;

                foreach ($topics as $topic) {
                    $dist          = $pm?->distributions->firstWhere('topic', $topic);
                    $given[$topic] = $dist ? (float) $dist->given_marks : null;

                    if ($dist) {
                        $total += (float) $dist->given_marks;
                        $count++;
                    }
                }

                $complete   = $count === count($topics);
                $percentage = $outOf > 0 ? round(($total / $outOf) * 100, 2) : 0;
                $grade      = ['grade' => 'I', 'point' => '-'];

                if ($complete) {
                    foreach (ProjectMark::GRADING_SCALE as $g) {
                        if ($percentage >= $g['min']) {
                            $grade = ['grade' => $g['grade'], 'point' => $g['point']];
                            break;
                        }
                    }
                }

                $rows->push([
                    'student'    => $student,
                    'marks'      => $given,
                    'total'      => $total,
                    'out_of'     => $outOf,
                    'percentage' => $percentage,
                    'grade'      => $grade['grade'],
                    'point'      => $grade['point'],
                    'complete'   => $complete,
                    'remarks'    => $pm?->remarks,
                    'supervisor' => $pm?->supervisor?->name,
                ]);
            }

            $sheets[$project->id] = $rows;
        }

        return $sheets;
    }

    /** Per-project summary used by the admin/teacher index tables. */
    protected function summaries(Collection $projects): array
    {
        $summaries = [];

        foreach ($this->buildSheets($projects) as $projectId => $rows) {
            $topicAvg = [];

            foreach ($this->topicMap() as $topic) {
                $vals = $rows->map(fn ($r) => $r['marks'][$topic])->filter(fn ($v) => $v !== null);
                $topicAvg[$topic] = $vals->count() ? round($vals->avg(), 2) : null;
            }

            $complete = $rows->where('complete', true);

            $summaries[$projectId] = [
                'students'  => $rows->count(),
                'complete'  => $complete->count(),
                'topic_avg' => $topicAvg,
                'avg_pct'   => $complete->count() ? round($complete->avg('percentage'), 2) : null,
            ];
        }

        return $summaries;
    }

    /** Grade + point for a percentage (used for project averages). */
    protected function gradeFor(float $percentage): array
    {
        foreach (\App\Models\ProjectMark::GRADING_SCALE as $g) {
            if ($percentage >= $g['min']) {
                return ['grade' => $g['grade'], 'point' => $g['point']];
            }
        }

        return ['grade' => 'F', 'point' => '0'];
    }
}