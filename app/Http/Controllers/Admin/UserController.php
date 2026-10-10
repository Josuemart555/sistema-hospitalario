<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\UsersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Option;
use App\Models\User;
use App\Services\MenuService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UsersDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('admin.users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.users.form', $this->formData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request, MenuService $menu): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $user = User::forceCreate([
                ...$request->safe()->only(['name', 'email', 'status']),
                'password' => Hash::make($request->input('password')),
                'email_verified_at' => now(),
                'two_factor_allowed' => $request->boolean('two_factor_allowed'),
            ]);
            $user->syncRoles(array_map('intval', $request->input('roles', [])));
            $user->syncPermissions(array_map('intval', $request->input('permissions', [])));
            $user->options()->sync($request->input('options', []));
            if ($request->hasFile('avatar')) {
                $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            }
        });
        $menu->flush();

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): RedirectResponse
    {
        return redirect()->route('admin.users.edit', $user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $user->load(['roles', 'permissions', 'options']);

        return view('admin.users.form', [...$this->formData(), 'user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user, MenuService $menu): RedirectResponse
    {
        if ($user->hasRole('Super Administrador') && ! in_array((string) $user->roles()->where('name', 'Super Administrador')->value('id'), array_map('strval', $request->input('roles', [])), true) && User::role('Super Administrador')->count() === 1) {
            return back()->withErrors(['roles' => 'Debe existir al menos un superadministrador.']);
        }

        DB::transaction(function () use ($request, $user): void {
            $user->update([...$request->safe()->only(['name', 'email', 'status']), 'two_factor_allowed' => $request->boolean('two_factor_allowed')]);
            $user->syncRoles(array_map('intval', $request->input('roles', [])));
            $user->syncPermissions(array_map('intval', $request->input('permissions', [])));
            $user->options()->sync($request->input('options', []));
            if ($request->hasFile('avatar')) {
                $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            }
        });
        $menu->flush();

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, MenuService $menu): RedirectResponse
    {
        if ($user->is(auth()->user()) || ($user->hasRole('Super Administrador') && User::role('Super Administrador')->count() === 1)) {
            return back()->withErrors(['user' => 'Este usuario no puede eliminarse.']);
        }
        $user->delete();
        $menu->flush();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado.');
    }

    private function formData(): array
    {
        return [
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'permissions' => Permission::query()->orderBy('name')->get(['id', 'name']),
            'options' => Option::tree(),
        ];
    }
}
