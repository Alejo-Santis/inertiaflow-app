<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tarea asignada</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 0; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 32px 36px; }
    .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 700; }
    .header p  { color: #c7d2fe; margin: 6px 0 0; font-size: 14px; }
    .body { padding: 32px 36px; }
    .body p { color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
    .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin: 20px 0; }
    .card .label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; }
    .card .value { font-size: 16px; font-weight: 700; color: #1e293b; margin: 4px 0 0; }
    .meta { display: flex; gap: 24px; margin-top: 16px; }
    .meta span { font-size: 13px; color: #64748b; }
    .meta strong { color: #374151; }
    .btn { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 10px; font-size: 14px; font-weight: 600; margin-top: 8px; }
    .footer { border-top: 1px solid #f1f5f9; padding: 20px 36px; text-align: center; font-size: 12px; color: #94a3b8; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>📋 Tarea asignada</h1>
      <p>InertiaFlow — Gestión de proyectos</p>
    </div>
    <div class="body">
      <p>Hola <strong>{{ $assignee->name }}</strong>,</p>
      <p><strong>{{ $assignedBy->name }}</strong> te ha asignado una nueva tarea en el proyecto <strong>{{ $task->project->name }}</strong>:</p>

      <div class="card">
        <div class="label">Tarea</div>
        <div class="value">{{ $task->title }}</div>
        @if($task->description)
          <p style="margin: 12px 0 0; font-size: 14px; color: #64748b;">{{ Str::limit($task->description, 200) }}</p>
        @endif
        <div class="meta">
          <span><strong>Prioridad:</strong> {{ ucfirst($task->priority) }}</span>
          @if($task->due_date)
            <span><strong>Vence:</strong> {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}</span>
          @endif
          @if($task->estimated_hours)
            <span><strong>Estimado:</strong> {{ $task->estimated_hours }}h</span>
          @endif
        </div>
      </div>

      <a href="{{ route('projects.tasks.show', [$task->project->uuid, $task->uuid]) }}" class="btn">Ver tarea →</a>
    </div>
    <div class="footer">
      © {{ date('Y') }} InertiaFlow. Este correo es automático, no respondas a él.
    </div>
  </div>
</body>
</html>
