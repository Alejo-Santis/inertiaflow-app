<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    protected $signature   = 'tasks:remind';
    protected $description = 'Envía recordatorios de tareas próximas a vencer o vencidas';

    public function handle(): void
    {
        $today    = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        // Tareas que vencen hoy
        $dueToday = Task::whereDate('due_date', $today)
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value])
            ->with(['assignees', 'project:id,uuid,name'])
            ->get();

        foreach ($dueToday as $task) {
            foreach ($task->assignees as $user) {
                NotificationService::send(
                    $user->id,
                    'task_due',
                    "⏰ Vence HOY: \"{$task->title}\"",
                    "Proyecto: {$task->project->name}",
                    route('projects.tasks.show', [$task->project->uuid, $task->uuid])
                );
            }
        }

        // Tareas que vencen mañana
        $dueTomorrow = Task::whereDate('due_date', $tomorrow)
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value])
            ->with(['assignees', 'project:id,uuid,name'])
            ->get();

        foreach ($dueTomorrow as $task) {
            foreach ($task->assignees as $user) {
                NotificationService::send(
                    $user->id,
                    'task_due',
                    "⏰ Vence mañana: \"{$task->title}\"",
                    "Proyecto: {$task->project->name}",
                    route('projects.tasks.show', [$task->project->uuid, $task->uuid])
                );
            }
        }

        // Tareas ya vencidas (notificar una vez al día)
        $overdue = Task::whereDate('due_date', '<', $today)
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value])
            ->with(['assignees', 'project:id,uuid,name'])
            ->get();

        foreach ($overdue as $task) {
            foreach ($task->assignees as $user) {
                NotificationService::send(
                    $user->id,
                    'task_due',
                    "🚨 Tarea vencida: \"{$task->title}\"",
                    "Venció el {$task->due_date} · Proyecto: {$task->project->name}",
                    route('projects.tasks.show', [$task->project->uuid, $task->uuid])
                );
            }
        }

        $total = $dueToday->count() + $dueTomorrow->count() + $overdue->count();
        $this->info("Recordatorios enviados para {$total} tareas.");
    }
}
