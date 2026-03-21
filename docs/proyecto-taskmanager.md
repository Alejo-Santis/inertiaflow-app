# 🗂️ TaskManager — Laravel + Inertia.js + Svelte 5

Guía completa de desarrollo paso a paso.

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Laravel 11 |
| Frontend | Svelte 5 + Inertia.js |
| Base de datos | MySQL / PostgreSQL |
| Estilos | Tailwind CSS v4 |
| Autenticación | Laravel Breeze (Inertia + Svelte) |

---

## Requisitos previos

- PHP >= 8.2
- Composer >= 2.x
- Node.js >= 20.x
- MySQL >= 8.0 o PostgreSQL >= 15
- Git

---

## Fase 1 — Instalación del proyecto

### 1.1 Crear el proyecto Laravel

```bash
composer create-project laravel/laravel taskmanager
cd taskmanager
```

### 1.2 Instalar Laravel Breeze con Inertia + Svelte

```bash
composer require laravel/breeze --dev
php artisan breeze:install
# Seleccionar: Svelte with Inertia
# Seleccionar: No (dark mode opcional)
# Seleccionar: PHPUnit o Pest (tu preferencia)
```

### 1.3 Instalar dependencias y compilar

```bash
npm install
npm run dev
```

### 1.4 Configurar la base de datos

Editar `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskmanager
DB_USERNAME=root
DB_PASSWORD=
```

Crear la base de datos y correr migraciones base:

```bash
php artisan migrate
```

---

## Fase 2 — Migraciones

Crear todas las migraciones en orden:

```bash
php artisan make:migration create_projects_table
php artisan make:migration create_project_members_table
php artisan make:migration create_tasks_table
php artisan make:migration create_task_assignments_table
php artisan make:migration create_comments_table
```

### 2.1 `create_projects_table`

```php
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
    $table->string('name');
    $table->text('description')->nullable();
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->string('status')->default('active');
    // 'active' | 'on_hold' | 'completed' | 'cancelled'
    $table->string('color', 7)->default('#6366f1');
    $table->timestamps();
    $table->softDeletes();
});
```

### 2.2 `create_project_members_table`

```php
Schema::create('project_members', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('role')->default('member');
    // 'owner' | 'admin' | 'member' | 'viewer'
    $table->timestamp('joined_at')->useCurrent();
    $table->unique(['project_id', 'user_id']);
});
```

### 2.3 `create_tasks_table`

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->foreignId('created_by')->constrained('users');
    $table->string('title');
    $table->text('description')->nullable();
    $table->tinyInteger('priority')->default(2);
    // 1=low | 2=medium | 3=high | 4=urgent
    $table->string('status')->default('todo');
    // 'todo' | 'in_progress' | 'in_review' | 'done' | 'cancelled'
    $table->date('due_date')->nullable();
    $table->unsignedSmallInteger('estimated_hours')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['project_id', 'status']);
    $table->index(['project_id', 'priority']);
});
```

### 2.4 `create_task_assignments_table`

```php
Schema::create('task_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('task_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('assigned_at')->useCurrent();
    $table->unique(['task_id', 'user_id']);
});
```

### 2.5 `create_comments_table`

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('task_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->text('body');
    $table->timestamps();
    $table->index('task_id');
});
```

### 2.6 Ejecutar todas las migraciones

```bash
php artisan migrate
```

---

## Fase 3 — Enums PHP 8.1+

```bash
mkdir -p app/Enums
```

**`app/Enums/ProjectStatus.php`**
```php
<?php
namespace App\Enums;

enum ProjectStatus: string {
    case Active    = 'active';
    case OnHold    = 'on_hold';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
```

**`app/Enums/TaskStatus.php`**
```php
<?php
namespace App\Enums;

enum TaskStatus: string {
    case Todo       = 'todo';
    case InProgress = 'in_progress';
    case InReview   = 'in_review';
    case Done       = 'done';
    case Cancelled  = 'cancelled';
}
```

**`app/Enums/TaskPriority.php`**
```php
<?php
namespace App\Enums;

enum TaskPriority: int {
    case Low    = 1;
    case Medium = 2;
    case High   = 3;
    case Urgent = 4;
}
```

**`app/Enums/ProjectRole.php`**
```php
<?php
namespace App\Enums;

enum ProjectRole: string {
    case Owner  = 'owner';
    case Admin  = 'admin';
    case Member = 'member';
    case Viewer = 'viewer';
}
```

---

## Fase 4 — Modelos Eloquent

```bash
php artisan make:model Project
php artisan make:model ProjectMember
php artisan make:model Task
php artisan make:model TaskAssignment
php artisan make:model Comment
```

### 4.1 `app/Models/Project.php`

```php
<?php
namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id', 'name', 'description',
        'start_date', 'end_date', 'status', 'color',
    ];

    protected $casts = [
        'status'     => ProjectStatus::class,
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
```

### 4.2 `app/Models/Task.php`

```php
<?php
namespace App\Models;

use App\Enums\{TaskStatus, TaskPriority};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'created_by', 'title', 'description',
        'priority', 'status', 'due_date', 'estimated_hours',
    ];

    protected $casts = [
        'status'   => TaskStatus::class,
        'priority' => TaskPriority::class,
        'due_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignments')
                    ->withPivot('assigned_at');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

### 4.3 `app/Models/Comment.php`

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = ['task_id', 'user_id', 'body'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## Fase 5 — Factories y Seeders

```bash
php artisan make:factory ProjectFactory --model=Project
php artisan make:factory TaskFactory --model=Task
php artisan make:seeder ProjectSeeder
```

### 5.1 `ProjectFactory`

```php
public function definition(): array
{
    return [
        'owner_id'    => User::factory(),
        'name'        => fake()->bs(),
        'description' => fake()->paragraph(),
        'start_date'  => fake()->dateTimeBetween('-1 month', 'now'),
        'end_date'    => fake()->dateTimeBetween('now', '+3 months'),
        'status'      => fake()->randomElement(['active', 'on_hold', 'completed']),
        'color'       => fake()->hexColor(),
    ];
}
```

### 5.2 `TaskFactory`

```php
public function definition(): array
{
    return [
        'project_id'       => Project::factory(),
        'created_by'       => User::factory(),
        'title'            => fake()->sentence(4),
        'description'      => fake()->paragraph(),
        'priority'         => fake()->numberBetween(1, 4),
        'status'           => fake()->randomElement(['todo', 'in_progress', 'done']),
        'due_date'         => fake()->dateTimeBetween('now', '+1 month'),
        'estimated_hours'  => fake()->numberBetween(1, 40),
    ];
}
```

### 5.3 `DatabaseSeeder.php`

```php
public function run(): void
{
    $users = User::factory(10)->create();

    Project::factory(5)->create(['owner_id' => $users->first()->id])
        ->each(function ($project) use ($users) {
            // Agregar miembros al proyecto
            $project->members()->attach(
                $users->random(4)->pluck('id'),
                ['role' => 'member', 'joined_at' => now()]
            );

            // Crear tareas para cada proyecto
            Task::factory(8)->create([
                'project_id' => $project->id,
                'created_by' => $users->first()->id,
            ])->each(function ($task) use ($users) {
                $task->assignees()->attach($users->random(2)->pluck('id'));
            });
        });
}
```

```bash
php artisan db:seed
```

---

## Fase 6 — Controllers (con Form Requests)

```bash
php artisan make:controller ProjectController --resource
php artisan make:controller TaskController --resource
php artisan make:controller CommentController

php artisan make:request StoreProjectRequest
php artisan make:request UpdateProjectRequest
php artisan make:request StoreTaskRequest
php artisan make:request UpdateTaskRequest
```

### 6.1 `ProjectController` (estructura base)

```php
<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\{StoreProjectRequest, UpdateProjectRequest};
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = auth()->user()->projects()
            ->withCount('tasks')
            ->latest()
            ->paginate(12);

        return Inertia::render('Projects/Index', compact('projects'));
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create([
            ...$request->validated(),
            'owner_id' => auth()->id(),
        ]);

        $project->members()->attach(auth()->id(), [
            'role'      => 'owner',
            'joined_at' => now(),
        ]);

        return redirect()->route('projects.show', $project);
    }

    public function show(Project $project)
    {
        $project->load(['owner', 'members', 'tasks.assignees']);

        return Inertia::render('Projects/Show', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return back();
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index');
    }
}
```

### 6.2 `TaskController` (estructura base)

```php
<?php
namespace App\Http\Controllers;

use App\Models\{Project, Task};
use App\Http\Requests\{StoreTaskRequest, UpdateTaskRequest};
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        $tasks = $project->tasks()
            ->with(['assignees', 'creator'])
            ->filter(request()->only(['status', 'priority', 'search']))
            ->paginate(20);

        return Inertia::render('Tasks/Index', compact('project', 'tasks'));
    }

    public function store(StoreTaskRequest $request, Project $project)
    {
        $task = $project->tasks()->create([
            ...$request->validated(),
            'created_by' => auth()->id(),
        ]);

        if ($request->has('assignee_ids')) {
            $task->assignees()->sync($request->assignee_ids);
        }

        return back();
    }

    public function show(Project $project, Task $task)
    {
        $task->load(['assignees', 'creator', 'comments.user']);

        return Inertia::render('Tasks/Show', compact('project', 'task'));
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task)
    {
        $task->update($request->validated());

        if ($request->has('assignee_ids')) {
            $task->assignees()->sync($request->assignee_ids);
        }

        return back();
    }

    public function destroy(Project $project, Task $task)
    {
        $task->delete();

        return back();
    }
}
```

---

## Fase 7 — Rutas

Editar `routes/web.php`:

```php
<?php
use App\Http\Controllers\{ProjectController, TaskController, CommentController};
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::resource('projects', ProjectController::class);

    Route::prefix('projects/{project}')->name('projects.')->group(function () {
        Route::resource('tasks', TaskController::class)->except(['create', 'edit']);
        Route::post('tasks/{task}/comments', [CommentController::class, 'store'])
             ->name('tasks.comments.store');
    });

});

require __DIR__.'/auth.php';
```

---

## Fase 8 — Páginas Svelte

Estructura de archivos en `resources/js/Pages/`:

```
Pages/
├── Projects/
│   ├── Index.svelte       ← listado + búsqueda
│   ├── Show.svelte        ← detalle del proyecto + lista de tareas
│   ├── Create.svelte      ← formulario nuevo proyecto
│   └── Edit.svelte        ← formulario editar proyecto
├── Tasks/
│   ├── Index.svelte       ← listado con filtros
│   ├── Show.svelte        ← detalle de tarea + comentarios
│   └── Partials/
│       ├── TaskForm.svelte
│       └── AssigneeSelect.svelte
└── Dashboard.svelte
```

### 8.1 Ejemplo: `Projects/Index.svelte`

```svelte
<script>
  import { router } from '@inertiajs/svelte'
  import { debounce } from 'lodash'

  let { projects } = $props()
  let search = $state('')

  const doSearch = debounce((val) => {
    router.get(route('projects.index'), { search: val }, {
      preserveState: true,
      replace: true,
    })
  }, 400)

  $effect(() => doSearch(search))

  function deleteProject(id) {
    if (!confirm('¿Eliminar este proyecto?')) return
    router.delete(route('projects.destroy', id))
  }
</script>

<div class="p-6">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold">Proyectos</h1>
    <a href={route('projects.create')}
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
      + Nuevo proyecto
    </a>
  </div>

  <input
    type="search"
    bind:value={search}
    placeholder="Buscar proyectos..."
    class="w-full border rounded-lg px-4 py-2 mb-6"
  />

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    {#each projects.data as project}
      <div class="bg-white rounded-xl shadow-sm border p-5">
        <div class="flex items-center gap-3 mb-2">
          <span class="w-3 h-3 rounded-full" style="background:{project.color}"></span>
          <h2 class="font-medium text-gray-900">{project.name}</h2>
        </div>
        <p class="text-sm text-gray-500 mb-4 line-clamp-2">{project.description}</p>
        <div class="flex justify-between items-center text-sm">
          <span class="text-gray-400">{project.tasks_count} tareas</span>
          <div class="flex gap-2">
            <a href={route('projects.show', project.id)} class="text-indigo-600 hover:underline">
              Ver
            </a>
            <button onclick={() => deleteProject(project.id)} class="text-red-500 hover:underline">
              Eliminar
            </button>
          </div>
        </div>
      </div>
    {/each}
  </div>
</div>
```

### 8.2 Ejemplo: `Tasks/Show.svelte` (fragmento — asignación de miembros)

```svelte
<script>
  import { router, useForm } from '@inertiajs/svelte'

  let { project, task } = $props()

  const form = useForm({
    assignee_ids: task.assignees.map(a => a.id),
  })

  function assign() {
    form.patch(route('projects.tasks.update', [project.id, task.id]))
  }
</script>

<div class="bg-white rounded-xl p-6 shadow-sm">
  <h1 class="text-xl font-semibold mb-2">{task.title}</h1>
  <p class="text-gray-600 mb-6">{task.description}</p>

  <label class="block text-sm font-medium mb-1">Asignados</label>
  <select multiple bind:value={$form.assignee_ids} class="w-full border rounded-lg p-2">
    {#each project.members as member}
      <option value={member.id}>{member.name}</option>
    {/each}
  </select>

  <button
    onclick={assign}
    disabled={$form.processing}
    class="mt-3 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
    Guardar asignación
  </button>
</div>
```

---

## Fase 9 — Scopes de búsqueda y filtros

Agregar en el modelo `Task`:

```php
public function scopeFilter($query, array $filters)
{
    $query->when($filters['search'] ?? null, fn($q, $v) =>
        $q->where('title', 'like', "%{$v}%")
          ->orWhere('description', 'like', "%{$v}%")
    );

    $query->when($filters['status'] ?? null, fn($q, $v) =>
        $q->where('status', $v)
    );

    $query->when($filters['priority'] ?? null, fn($q, $v) =>
        $q->where('priority', $v)
    );
}
```

---

## Fase 10 — Políticas de autorización

```bash
php artisan make:policy ProjectPolicy --model=Project
php artisan make:policy TaskPolicy --model=Task
```

Registrar en `AppServiceProvider` o usar el descubrimiento automático de Laravel.

Ejemplo en `ProjectPolicy`:

```php
public function update(User $user, Project $project): bool
{
    return $user->id === $project->owner_id
        || $project->members()
                   ->where('user_id', $user->id)
                   ->whereIn('role', ['owner', 'admin'])
                   ->exists();
}

public function delete(User $user, Project $project): bool
{
    return $user->id === $project->owner_id;
}
```

---

## Checklist de desarrollo

### Backend
- [ ] Migraciones creadas y ejecutadas
- [ ] Enums definidos
- [ ] Modelos con relaciones y casts
- [ ] Factories y Seeders funcionando
- [ ] Form Requests con validaciones
- [ ] Controllers con lógica CRUD
- [ ] Rutas definidas y nombradas
- [ ] Policies de autorización

### Frontend
- [ ] `Projects/Index.svelte` — listado + búsqueda
- [ ] `Projects/Show.svelte` — detalle + tareas del proyecto
- [ ] `Projects/Create.svelte` — formulario
- [ ] `Projects/Edit.svelte` — formulario edición
- [ ] `Tasks/Index.svelte` — listado con filtros
- [ ] `Tasks/Show.svelte` — detalle + asignados + comentarios
- [ ] Componente `AssigneeSelect.svelte`
- [ ] Componente `TaskForm.svelte`

### Extras opcionales
- [ ] Paginación en listados
- [ ] Toast notifications con `useForm`
- [ ] Kanban board (`Tasks/Kanban.svelte`)
- [ ] Exportar tareas a CSV
- [ ] Notificaciones por email al asignar una tarea

---

## Comandos útiles de referencia

```bash
# Limpiar caché
php artisan optimize:clear

# Ver rutas registradas
php artisan route:list --name=projects

# Correr pruebas
php artisan test

# Tinker (explorar modelos)
php artisan tinker

# Recompilar frontend
npm run build

# Dev server
php artisan serve & npm run dev
```

---

## Estructura final de directorios

```
taskmanager/
├── app/
│   ├── Enums/
│   │   ├── ProjectStatus.php
│   │   ├── TaskStatus.php
│   │   ├── TaskPriority.php
│   │   └── ProjectRole.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ProjectController.php
│   │   │   ├── TaskController.php
│   │   │   └── CommentController.php
│   │   └── Requests/
│   │       ├── StoreProjectRequest.php
│   │       ├── UpdateProjectRequest.php
│   │       ├── StoreTaskRequest.php
│   │       └── UpdateTaskRequest.php
│   ├── Models/
│   │   ├── Project.php
│   │   ├── ProjectMember.php
│   │   ├── Task.php
│   │   ├── TaskAssignment.php
│   │   └── Comment.php
│   └── Policies/
│       ├── ProjectPolicy.php
│       └── TaskPolicy.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
└── resources/js/
    ├── Pages/
    │   ├── Projects/
    │   └── Tasks/
    └── Components/
```
