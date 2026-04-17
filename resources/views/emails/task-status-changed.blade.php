<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Estado de tarea actualizado</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 0; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #8b5cf6, #6366f1); padding: 32px 36px; }
    .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 700; }
    .header p  { color: #ddd6fe; margin: 6px 0 0; font-size: 14px; }
    .body { padding: 32px 36px; }
    .body p { color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
    .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin: 20px 0; }
    .card .label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; }
    .card .value { font-size: 16px; font-weight: 700; color: #1e293b; margin: 4px 0 0; }
    .status-change { display: flex; align-items: center; gap: 12px; margin: 20px 0; padding: 16px 20px; background: #fafafa; border-radius: 10px; border: 1px solid #e2e8f0; }
    .status-pill { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
    .status-arrow { color: #94a3b8; font-size: 18px; }
    .pill-todo        { background: #f1f5f9; color: #64748b; }
    .pill-in_progress { background: #dbeafe; color: #1d4ed8; }
    .pill-in_review   { background: #fef3c7; color: #92400e; }
    .pill-done        { background: #dcfce7; color: #166534; }
    .pill-cancelled   { background: #fee2e2; color: #991b1b; }
    .btn { display: inline-block; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 10px; font-size: 14px; font-weight: 600; margin-top: 8px; }
    .footer { border-top: 1px solid #f1f5f9; padding: 20px 36px; text-align: center; font-size: 12px; color: #94a3b8; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>🔄 Estado actualizado</h1>
      <p>InertiaFlow — Gestión de proyectos</p>
    </div>
    <div class="body">
      <p>Hola <strong>{{ $recipient->name }}</strong>,</p>
      <p><strong>{{ $changedBy->name }}</strong> cambió el estado de una tarea en <strong>{{ $task->project->name }}</strong>:</p>

      <div class="card">
        <div class="label">Tarea</div>
        <div class="value">{{ $task->title }}</div>
      </div>

      @php
        $labels = [
          'todo'        => 'Por hacer',
          'in_progress' => 'En progreso',
          'in_review'   => 'En revisión',
          'done'        => 'Completada',
          'cancelled'   => 'Cancelada',
        ];
      @endphp

      <div class="status-change">
        <span class="status-pill pill-{{ $previousStatus }}">{{ $labels[$previousStatus] ?? $previousStatus }}</span>
        <span class="status-arrow">→</span>
        <span class="status-pill pill-{{ $task->status }}">{{ $labels[$task->status] ?? $task->status }}</span>
      </div>

      <a href="{{ route('projects.tasks.show', [$task->project->uuid, $task->uuid]) }}" class="btn">Ver tarea →</a>
    </div>
    <div class="footer">
      © {{ date('Y') }} InertiaFlow. Este correo es automático, no respondas a él.
    </div>
  </div>
</body>
</html>
