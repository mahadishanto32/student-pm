<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PartnerApproval;
use App\Notifications\PartnerApprovalResponseNotification;
use App\Services\ProjectNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentPartnerApprovalController extends Controller
{
    public function respond(Request $request, PartnerApproval $approval)
    {
        abort_unless($approval->user_id === auth()->id(), 403);
        abort_unless($approval->approve === 'Pending', 404);

        $data = $request->validate([
            'approve' => ['required', 'in:Yes,No'],
        ]);

        $project = $approval->project;
        $user    = auth()->user();
        $inviter = $approval->inviter;

        if ($data['approve'] === 'Yes') {
            DB::transaction(function () use ($approval, $project) {
                $approval->update(['approve' => 'Yes']);
                $project->teamMembers()->syncWithoutDetaching([$approval->user_id]);
            });

            $inviter?->notify(new PartnerApprovalResponseNotification(
                $project->project_name, $user->name, 'Yes', $project->id
            ));

            // Creation flow: everyone accepted -> now notify admins/members
            if ($approval->for_creation) {
                $stillPending = $project->partnerApprovals()
                    ->where('for_creation', true)->pending()->exists();

                if (! $stillPending) {
                    app(ProjectNotificationService::class)->projectCreated($project);
                }
            }

            return redirect()
                ->route('student.projects.show', $project->id)
                ->with('success', 'You joined the project.');
        }

        // ---- Declined ----
        $projectName = $project->project_name;

        if ($approval->for_creation) {
            // Declined during creation -> the project is not created
            DB::transaction(function () use ($project) {
                $project->teamMembers()->detach();
                $project->delete(); // approvals cascade
            });

            $inviter?->notify(new PartnerApprovalResponseNotification(
                $projectName, $user->name, 'No', null, true
            ));
        } else {
            $approval->update(['approve' => 'No']);

            $inviter?->notify(new PartnerApprovalResponseNotification(
                $projectName, $user->name, 'No', $project->id
            ));
        }

        return redirect()
            ->route('student.projects.index')
            ->with('success', 'You declined the invitation.');
    }
}