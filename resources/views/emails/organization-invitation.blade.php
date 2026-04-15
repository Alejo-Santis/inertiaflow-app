<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invitación a {{ $invitation->organization->name }}</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 0; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 32px 36px; }
    .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 700; }
    .header p  { color: #c7d2fe; margin: 6px 0 0; font-size: 14px; }
    .body { padding: 32px 36px; }
    .body p { color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
    .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin: 20px 0; }
    .card .org-name { font-size: 20px; font-weight: 700; color: #1e293b; }
    .card .meta { display: flex; gap: 24px; margin-top: 12px; flex-wrap: wrap; }
    .card .meta span { font-size: 13px; color: #64748b; }
    .card .meta strong { color: #374151; }
    .badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #ede9fe; color: #5b21b6; }
    .btn { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff !important; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; font-weight: 600; margin-top: 8px; }
    .warning { background: #fefce8; border: 1px solid #fde047; border-radius: 10px; padding: 12px 16px; margin-top: 20px; font-size: 13px; color: #713f12; }
    .footer { border-top: 1px solid #f1f5f9; padding: 20px 36px; text-align: center; font-size: 12px; color: #94a3b8; }
    .link-fallback { word-break: break-all; font-size: 12px; color: #6366f1; margin-top: 16px; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>Invitación a organización</h1>
      <p>InertiaFlow — Gestión de proyectos tecnológicos</p>
    </div>
    <div class="body">
      <p>Hola,</p>
      <p>
        <strong>{{ $invitation->inviter->name }}</strong> te ha invitado a unirte a la organización
        <strong>{{ $invitation->organization->name }}</strong> en InertiaFlow.
      </p>

      <div class="card">
        <div class="org-name">{{ $invitation->organization->name }}</div>
        @if($invitation->organization->description)
          <p style="margin: 8px 0 0; font-size: 14px; color: #64748b;">{{ $invitation->organization->description }}</p>
        @endif
        <div class="meta">
          <span>
            Tu rol: <strong>
              @php
                $roleLabels = ['owner'=>'Owner','admin'=>'Admin','manager'=>'Manager','member'=>'Member'];
              @endphp
              {{ $roleLabels[$invitation->role] ?? ucfirst($invitation->role) }}
            </strong>
          </span>
          <span>Invitado por: <strong>{{ $invitation->inviter->name }}</strong></span>
          <span>Válido hasta: <strong>{{ $invitation->expires_at->format('d/m/Y H:i') }}</strong></span>
        </div>
      </div>

      <p>Haz clic en el botón de abajo para aceptar la invitación:</p>

      <a href="{{ $invitation->getAcceptUrl() }}" class="btn">Aceptar invitación →</a>

      <div class="warning">
        ⚠️ Este enlace expira el {{ $invitation->expires_at->format('d/m/Y') }} a las {{ $invitation->expires_at->format('H:i') }}.
        Si no esperabas esta invitación, simplemente ignora este correo.
      </div>

      <p class="link-fallback">
        Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
        {{ $invitation->getAcceptUrl() }}
      </p>
    </div>
    <div class="footer">
      © {{ date('Y') }} InertiaFlow. Este correo es automático, no respondas a él.
    </div>
  </div>
</body>
</html>
