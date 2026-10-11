<?php

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectBook;
use App\Models\ProjectMilestone;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Shared dashboard queries for admin / teacher / student.
 *
 * Pass a collection of project ids to scope everything to those projects,
 * or null for "all projects" (admin). Uses grouped queries instead of one
 * COUNT() per status, so each dashboard runs far fewer queries.
 */
class DashboardMetrics
{
    public const PROJECT_STATUSES = ['pending', 'approved', 'working', 'completed', 'rejected', 'cancelled'];

    public function __construct(protected ?Collection $projectIds = null)
    {
    }

    protected function scope($query, string $column = 'project_id')
    {
        return $this->projectIds === null ? $query : $query->whereIn($column, $this->projectIds);
    }

    protected function openStatuses(): array
    {
        return [ProjectMilestone::STATUS_PENDING, ProjectMilestone::STATUS_NEED_CORRECTION];
    }

    /** Headline numbers. */
    public function counters(): array
    {
        $now = Carbon::now();

        $row = $this->scope(ProjectMilestone::query())->selectRaw(
            'COUNT(*) AS total,
             COALESCE(SUM(status = ?), 0) AS pending,
             COALESCE(SUM(status = ?), 0) AS completed,
             COALESCE(SUM(status IN (?, ?) AND tentative_time < ?), 0) AS overdue,
             COALESCE(SUM(status IN (?, ?) AND DATE(tentative_time) = ?), 0) AS due_today',
            [
                ProjectMilestone::STATUS_PENDING,
                ProjectMilestone::STATUS_COMPLETED,
                ...$this->openStatuses(), $now->toDateTimeString(),
                ...$this->openStatuses(), $now->toDateString(),
            ]
        )->first();

        $total = (int) $row->total;

        return [
            'projects'    => $this->scope(Project::query(), 'id')->count(),
            'books'       => $this->scope(ProjectBook::query())->count(),
            'milestones'  => $total,
            'pending'     => (int) $row->pending,
            'completed'   => (int) $row->completed,
            'overdue'     => (int) $row->overdue,
            'due_today'   => (int) $row->due_today,
            'completion'  => $total > 0 ? (int) round($row->completed / $total * 100) : 0,
        ];
    }

    /** Project book counts keyed by status (every status present). */
    public function bookStats(): Collection
    {
        $counts = $this->scope(ProjectBook::query())
            ->selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status');

        return collect(ProjectBook::statuses())
            ->mapWithKeys(fn ($s) => [$s => (int) ($counts[$s] ?? 0)]);
    }

    /** Project counts keyed by status. */
    public function projectStatusStats(): Collection
    {
        $counts = $this->scope(Project::query(), 'id')
            ->selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status');

        return collect(self::PROJECT_STATUSES)
            ->mapWithKeys(fn ($s) => [$s => (int) ($counts[$s] ?? 0)]);
    }

    /** Milestone status breakdown with counts and percentages. */
    public function milestoneStats(): Collection
    {
        $counts = $this->scope(ProjectMilestone::query())
            ->selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status');
        $total = (int) $counts->sum();

        return collect([
            ProjectMilestone::STATUS_PENDING,
            ProjectMilestone::STATUS_NEED_CORRECTION,
            ProjectMilestone::STATUS_COMPLETED,
            ProjectMilestone::STATUS_REJECTED,
        ])->map(function ($status) use ($counts, $total) {
            $count = (int) ($counts[$status] ?? 0);

            return [
                'status'  => $status,
                'label'   => ucwords(str_replace('_', ' ', $status)),
                'count'   => $count,
                'percent' => $total > 0 ? (int) round($count / $total * 100) : 0,
            ];
        });
    }

    /** Milestones created vs. completed per month for the last 7 months. */
    public function monthlyActivity(bool $withProjects = false): array
    {
        $start  = Carbon::now()->startOfMonth()->subMonths(6);
        $months = collect(range(0, 6))->map(fn ($i) => $start->copy()->addMonths($i));

        $created = $this->scope(ProjectMilestone::query())
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS c")
            ->groupBy('ym')->pluck('c', 'ym');

        $completed = $this->scope(ProjectMilestone::query())
            ->where('status', ProjectMilestone::STATUS_COMPLETED)
            ->where('updated_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m') AS ym, COUNT(*) AS c")
            ->groupBy('ym')->pluck('c', 'ym');

        $data = [
            'labels'    => $months->map->format('M')->all(),
            'created'   => $months->map(fn ($m) => (int) ($created[$m->format('Y-m')] ?? 0))->all(),
            'completed' => $months->map(fn ($m) => (int) ($completed[$m->format('Y-m')] ?? 0))->all(),
        ];

        if ($withProjects) {
            $projects = $this->scope(Project::query(), 'id')
                ->where('created_at', '>=', $start)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS c")
                ->groupBy('ym')->pluck('c', 'ym');
            $data['projects'] = $months->map(fn ($m) => (int) ($projects[$m->format('Y-m')] ?? 0))->all();
        }

        return $data;
    }

    /**
     * Project cards: milestone progress, elapsed time, team and supervisor.
     * "behind" is true when elapsed time runs 20+ points ahead of progress.
     */
    public function projectCards(int $limit = 5): Collection
    {
        $projects = $this->scope(Project::with('teacher'), 'id')->latest()->take($limit)->get();
        $ids      = $projects->pluck('id');

        $milestones = ProjectMilestone::whereIn('project_id', $ids)
            ->selectRaw('project_id, COUNT(*) AS total, COALESCE(SUM(status = ?), 0) AS done', [ProjectMilestone::STATUS_COMPLETED])
            ->groupBy('project_id')->get()->keyBy('project_id');

        $teams = DB::table('project_user')
            ->join('users', 'users.id', '=', 'project_user.user_id')
            ->whereIn('project_user.project_id', $ids)
            ->select('project_user.project_id', 'users.id', 'users.name')
            ->get()->groupBy('project_id');

        $now = Carbon::now();

        return $projects->map(function ($p) use ($milestones, $teams, $now) {
            $total    = (int) ($milestones[$p->id]->total ?? 0);
            $done     = (int) ($milestones[$p->id]->done ?? 0);
            $progress = $total > 0 ? (int) round($done / $total * 100) : 0;

            $elapsed = $daysLeft = $end = null;
            if ($p->start_date && $p->tentative_end_date) {
                $s    = Carbon::parse($p->start_date)->startOfDay();
                $e    = Carbon::parse($p->tentative_end_date)->endOfDay();
                $span = max(1, $e->timestamp - $s->timestamp);

                $elapsed  = (int) round(min(100, max(0, ($now->timestamp - $s->timestamp) / $span * 100)));
                $daysLeft = (int) ceil(($e->timestamp - $now->timestamp) / 86400);
                $end      = $e;
            }

            return [
                'id'       => $p->id,
                'name'     => $p->project_name,
                'topic'    => $p->project_topic,
                'group'    => $p->group_number,
                'status'   => $p->status,
                'teacher'  => $p->teacher->name ?? null,
                'team'     => ($teams[$p->id] ?? collect())->pluck('name')->all(),
                'total'    => $total,
                'done'     => $done,
                'progress' => $progress,
                'elapsed'  => $elapsed,
                'daysLeft' => $daysLeft,
                'end'      => $end,
                'behind'   => $elapsed !== null
                    && ! in_array($p->status, ['completed', 'cancelled', 'rejected'], true)
                    && ($elapsed - $progress) >= 20,
            ];
        });
    }

    /** Open milestones, most urgent first (overdue, then today, then upcoming). */
    public function attention(int $limit = 5): Collection
    {
        return $this->scope(ProjectMilestone::with('project'))
            ->whereIn('status', $this->openStatuses())
            ->orderByRaw('tentative_time IS NULL, tentative_time ASC')
            ->take($limit)->get();
    }

    public function recentUpdates(int $limit = 5): Collection
    {
        return $this->scope(ProjectMilestone::with(['project', 'doneBy']))
            ->latest('updated_at')->take($limit)->get();
    }

    /** Upcoming meetings and presentations merged into one date-ordered list. */
    public function schedule(int $limit = 5): Collection
    {
        $today = Carbon::today();

        $meetings = DB::table('meetings')
            ->join('projects', 'projects.id', '=', 'meetings.project_id')
            ->when($this->projectIds !== null, fn ($q) => $q->whereIn('meetings.project_id', $this->projectIds))
            ->where('meetings.meeting_date_and_time', '>=', $today)
            ->orderBy('meetings.meeting_date_and_time')->take($limit)
            ->get(['meetings.title', 'meetings.platform', 'meetings.meeting_date_and_time AS at', 'projects.project_name'])
            ->map(fn ($m) => [
                'kind' => 'meeting', 'title' => $m->title, 'project' => $m->project_name,
                'at' => Carbon::parse($m->at), 'meta' => ucfirst($m->platform),
            ]);

        $presentations = DB::table('presentations')
            ->join('projects', 'projects.id', '=', 'presentations.project_id')
            ->when($this->projectIds !== null, fn ($q) => $q->whereIn('presentations.project_id', $this->projectIds))
            ->where('presentations.date_of_presentation', '>=', $today)
            ->orderBy('presentations.date_of_presentation')->take($limit)
            ->get(['presentations.title', 'presentations.status', 'presentations.date_of_presentation AS at', 'projects.project_name'])
            ->map(fn ($p) => [
                'kind' => 'presentation', 'title' => $p->title, 'project' => $p->project_name,
                'at' => Carbon::parse($p->at), 'meta' => ucfirst($p->status),
            ]);

        return $meetings->concat($presentations)->sortBy('at')->take($limit)->values();
    }

    /** Unread notifications for a user. */
    public static function notifications($user, int $limit = 5): array
    {
        return [
            'unread' => $user->unreadNotifications()->count(),
            'items'  => $user->unreadNotifications()->latest()->take($limit)->get(),
        ];
    }

    /** Greeting that matches the time of day. */
    public static function greeting(): string
    {
        $h = now()->hour;

        return $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
    }
}
