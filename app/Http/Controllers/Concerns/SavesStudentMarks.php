<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ProjectMark;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait SavesStudentMarks
{
    /**
     * Save marks (out of 20) for one topic per student into
     * project_marks + marks_distributions.
     *
     * @param array $studentIds valid student ids of the project
     * @param array $marks      [student_id => ['given_marks' => x, 'remarks' => y]]
     */
    protected function saveTopicMarks(int $projectId, array $studentIds, string $topic, array $marks): void
    {
        $studentIds = array_map('intval', $studentIds);
        $now = now();

        foreach ($marks as $studentId => $data) {
            if (! in_array((int) $studentId, $studentIds, true)) {
                continue;
            }

            $projectMark = ProjectMark::firstOrNew([
                'project_id' => $projectId,
                'student_id' => $studentId,
            ]);
            $projectMark->supervisor_id = Auth::id();

            // remarks is one column per student/project, shared by all topics,
            // so only overwrite it when something was typed
            if (filled($data['remarks'] ?? null)) {
                $projectMark->remarks = $data['remarks'];
            }
            $projectMark->save();

            $query = DB::table('marks_distributions')
                ->where('project_marks_id', $projectMark->id)
                ->where('topic', $topic);

            $values = [
                'given_marks' => $data['given_marks'],
                'out_of'      => ProjectMark::MARKS_PER_TOPIC,
                'updated_at'  => $now,
            ];

            if ($query->exists()) {
                $query->update($values);
            } else {
                DB::table('marks_distributions')->insert($values + [
                    'project_marks_id' => $projectMark->id,
                    'topic'            => $topic,
                    'created_at'       => $now,
                ]);
            }
        }
    }
}