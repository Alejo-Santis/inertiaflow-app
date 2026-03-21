# InertiaFlow

Aplicación de gestión de proyectos y tareas para equipos internos, construida con **Laravel 13**, **Inertia.js 2** y **Svelte 5**.

---

## Stack tecnológico

| Capa | Tecnología |
| --- | --- |
| Backend | PHP 8.3 · Laravel 13 |
| Frontend | Svelte 5 · Inertia.js 2 · Tailwind CSS 4 |
| Base de datos | PostgreSQL |
| Build tool | Vite 7 |
| Autenticación | Laravel Session Auth |
| Roles y permisos | Spatie Laravel Permission 7 |
| Rutas frontend | Ziggy |
| Alertas | SweetAlert2 |

---

## Funcionalidades

- **Proyectos** — crear, editar, eliminar, gestionar miembros del equipo
- **Tareas** — CRUD completo, prioridades (1–4), estados, asignación, fecha límite, horas estimadas
- **Kanban** — tablero drag & drop por estado de tarea
- **Registro de tiempo** — horas logueadas vs estimadas por tarea con barra de progreso
- **Comentarios** — en cada tarea, con eliminación
- **Analíticas** — KPIs globales, gráfico de actividad 30 días, rendimiento por proyecto
- **Notificaciones** — campana en navbar con las 8 tareas más recientemente actualizadas
- **Gestión de usuarios** — solo admin: crear, editar, asignar roles
- **Recuperación de contraseña** — flujo completo por correo electrónico
- **Perfil de usuario** — editar nombre/email y cambiar contraseña

---

## Roles

| Rol | Capacidades |
| --- | --- |
| `admin` | Acceso total, gestión de usuarios del sistema |
| `manager` | Crear y gestionar proyectos |
| `member` | Ver y trabajar en proyectos asignados |

> El registro público está deshabilitado. Solo un administrador puede crear cuentas.

---

## Requisitos previos

- PHP >= 8.3 con extensiones: `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- Composer >= 2.x
- Node.js >= 20.x · npm >= 10.x
- PostgreSQL >= 14
- (Producción) Nginx o Apache2

---

## Instalación local

```bash
# 1. Clonar el repositorio
git clone <url-del-repo> inertiaflow
cd inertiaflow

# 2. Dependencias PHP y JS
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Editar .env — al menos: DB_*, MAIL_*, APP_URL
#    Ver sección "Variables de entorno" más abajo

# 5. Crear base de datos
psql -U postgres -c "CREATE DATABASE inertiaflow_db;"

# 6. Migraciones + datos iniciales
php artisan migrate --seed

# 7. Compilar assets y levantar servidor
npm run dev          # en una terminal (HMR)
php artisan serve    # en otra terminal
```

Accede en `http://localhost:8000`.

### Credenciales iniciales

| Email | Contraseña | Rol |
| --- | --- | --- |
| `admin@example.com` | `password` | admin |
| `demo@example.com` | `password` | member |

> **Cambia estas contraseñas antes de compartir el acceso.**

---

## Variables de entorno clave

```env
APP_NAME=InertiaFlow
APP_ENV=production
APP_DEBUG=false              # NUNCA true en producción
APP_URL=https://tudominio.com
APP_LOCALE=es

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inertiaflow_db
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña_segura

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_LIFETIME=120

QUEUE_CONNECTION=database
CACHE_STORE=database

# Proveedor de correo real (Mailgun, Resend, SES, Postmark…)
MAIL_MAILER=smtp
MAIL_HOST=smtp.tuproveedor.com
MAIL_PORT=587
MAIL_USERNAME=usuario_smtp
MAIL_PASSWORD=contraseña_smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Estructura del proyecto

```text
app/
├── Http/
│   ├── Controllers/        # Un controlador por módulo
│   └── Middleware/         # HandleInertiaRequests (props compartidos)
├── Models/                 # Eloquent: User, Project, Task, TimeLog, Comment
└── Policies/               # Autorización por recurso

database/
├── migrations/             # Esquema de la BD (11 migraciones)
└── seeders/                # Admin inicial + roles Spatie

resources/js/Pages/
├── Auth/                   # Login · ForgotPassword · ResetPassword
├── Analytics/              # Dashboard de analíticas
├── Dashboard/              # Vista de inicio
├── Profile/                # Perfil de usuario
├── Projects/               # Index · Create · Edit · Show
├── Tasks/                  # Index · Create · Edit · Show · Kanban
├── Users/                  # Gestión de usuarios (solo admin)
└── Layout.svelte           # Layout principal con navbar

routes/
└── web.php                 # 44 rutas organizadas por contexto
```

---

## Comandos útiles

```bash
# Limpiar cachés
php artisan optimize:clear

# Optimizar para producción
php artisan optimize

# Ver rutas registradas
php artisan route:list

# Queue worker para envío de correos
php artisan queue:work --sleep=3 --tries=3

# Consola interactiva
php artisan tinker
```

---

## Despliegue en producción

Ver **[DEPLOY.md](DEPLOY.md)** para instrucciones completas con Nginx y Apache2, incluyendo configuración de SSL, permisos, queue worker con Supervisor y checklist final.

---

## Licencia

Uso interno. Todos los derechos reservados.
