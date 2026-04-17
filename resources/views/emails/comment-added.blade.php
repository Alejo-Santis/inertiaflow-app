<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nuevo comentario</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 0; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #06b6d4, #3b82f6); padding: 32px 36px; }
    .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 700; }
    .header p  { color: #bfdbfe; margin: 6px 0 0; font-size: 14px; }
    .body { padding: 32px 36px; }
    .body p { color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
    .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin: 20px 0; }
    .card .label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; }
    .card .value { font-size: 16px; font-weight: 700; color: #1e293b; margin: 4px 0 0; }
    .comment-box { background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid #3b82f6; border-radius: 8px; padding: 16px 20px; margin: 16px 0; font-size: 14px; color: #374151; line-height: 1.6; }
    .btn { display: inline-block; background: linear-gradient(135deg, #06b6d4, #3b82f6); color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 10px; font-size: 14px; font-weight: 600; margin-top: 8px; }
    .footer { border-top: 1px solid #f1f5f9; padding: 20px 36px; text-align: center; font-size: 12px; color: #94a3b8; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>💬 Nuevo comentario</h1>
      <p>InertiaFlow — Gestión de proyectos</p>
    </div>
    <div class="body">
      <p>Hola <strong>{{ $recipient->name }}</strong>,</p>
      <p>
        @if($reason === 'mentioned')
          <strong>{{ $comment->user->name }}</strong> te mencionó en un comentario de la tarea:
        @else
          <strong>{{ $comment->user->name }}</strong> comentó en una tarea que te está asignada:
        @endif
      </p>

      <div class="card">
        <div class="label">Tarea</div>
        <div class="value">{{ $task->title }}</div>
      </div>

      <div class="comment-box">
        {{ Str::limit($comment->body, 400) }}
      </div>

      <a href="{{ route('projects.tasks.show', [$task->project->uuid, $task->uuid]) }}" class="btn">Ver tarea →</a>
    </div>
    <div class="footer">
      © {{ date('Y') }} InertiaFlow. Este correo es automático, no respondas a él.
    </div>
  </div>
</body>
</html>
