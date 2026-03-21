<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private function ensureAdmin(): void
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Solo los administradores pueden gestionar usuarios.');
        }
    }

    public function index()
    {
        $this->ensureAdmin();

        $users = User::with('roles')
            ->latest()
            ->paginate(15);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        $this->ensureAdmin();

        $roles = Role::orderBy('name')->get(['name']);

        return Inertia::render('Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'role'                  => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('success', "Usuario \"{$user->name}\" creado con éxito.");
    }

    public function edit(User $user)
    {
        $this->ensureAdmin();

        $roles    = Role::orderBy('name')->get(['name']);
        $userRole = $user->roles->first()?->name;

        return Inertia::render('Users/Edit', [
            'user'     => $user->load('roles'),
            'roles'    => $roles,
            'userRole' => $userRole,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdmin();

        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'role'  => ['required', 'string', 'exists:roles,name'],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', "Usuario \"{$user->name}\" actualizado con éxito.");
    }

    public function destroy(User $user)
    {
        $this->ensureAdmin();

        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Usuario \"{$userName}\" eliminado con éxito.");
    }
}
