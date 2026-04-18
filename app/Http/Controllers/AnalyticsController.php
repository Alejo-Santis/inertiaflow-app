<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Proyectos accesibles: propios, miembro directo o de orgs donde pertenece
        $orgIds = $user->organizationMemberships()->pluck('organization_id');

        $projectIds = Project::where('owner_id', $user->id)
            ->orWhereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->orWhereIn('organization_id', $orgIds)
            ->pluck('id');

        // Stats globales
        $totalTasks    = Task::whereIn('project_id', $projectIds)->count();
        $doneTasks     = Task::whereIn('project_id', $projectIds)->where('status', TaskStatus::Done->value)->count();
        $overdueTasks  = Task::whereIn('project_id', $projectIds)
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value])
            ->whereDate('due_date', '<', today())
            ->count();
        $cancelledTasks = Task::whereIn('project_id', $projectIds)->where('status', TaskStatus::Cancelled->value)->count();

        // Tareas por estado
        $byStatus = Task::whereIn('project_id', $projectIds)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Tareas por prioridad
        $byPriority = Task::whereIn('project_id', $projectIds)
            ->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->orderBy('priority')
            ->pluck('total', 'priority');

        // Stats por proyecto
        $projects = Project::whereIn('id', $projectIds)
            ->withCount([
                'tasks',
                'tasks as done_tasks_count'      => fn ($q) => $q->where('status', TaskStatus::Done->value),
                'tasks as overdue_tasks_count'   => fn ($q) => $q->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value])->whereDate('due_date', '<', today()),
                'tasks as inprogress_tasks_count'=> fn ($q) => $q->where('status', TaskStatus::InProgress->value),
            ])
            ->orderByDesc('tasks_count')
            ->get(['id', 'name', 'color', 'status', 'uuid']);

        // Tareas creadas en los últimos 30 días (actividad)
        $activity = Task::whereIn('project_id', $projectIds)
            ->where('created_at', '>=', now()->subDays(29))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        return Inertia::render('Analytics/Index', [
            'stats' => [
                'total_tasks'     => $totalTasks,
                'done_tasks'      => $doneTasks,
                'overdue_tasks'   => $overdueTasks,
                'cancelled_tasks' => $cancelledTasks,
                'completion_rate' => $totalTasks > 0 ? round($doneTasks / $totalTasks * 100) : 0,
            ],
            'by_status'   => $byStatus,
            'by_priority' => $byPriority,
            'projects'    => $projects,
            'activity'    => $activity,
        ]);
    }
}
