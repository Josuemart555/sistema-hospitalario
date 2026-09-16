<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Models\Option;
use App\Models\Role;
use App\Services\MenuService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', ['roles' => Role::withCount(['users', 'permissions'])->orderBy('name')->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.roles.form', $this->formData());
    }

    public function store(StoreRoleRequest $request, MenuService $menu): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $role = Role::create(['name' => $request->string('name'), 'guard_name' => 'web']);
            $role->syncPermissions($request->input('permissions', []));
            $role->options()->sync($request->input('options', []));
        });
        $menu->flush();

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function show(Role $role): RedirectResponse
    {
        return redirect()->route('admin.roles.edit', $role);
    }

    public function edit(Role $role): View
    {
        $role->load(['permissions', 'options']);

        return view('admin.roles.form', [...$this->formData(), 'role' => $role]);
    }

    public function update(StoreRoleRequest $request, Role $role, MenuService $menu): RedirectResponse
    {
        abort_if($role->name === 'Super Administrador' && $request->string('name')->toString() !== 'Super Administrador', 422, 'El rol protegido no puede renombrarse.');
        DB::transaction(function () use ($request, $role): void {
            $role->update(['name' => $request->string('name')]);
            $role->syncPermissions($request->input('permissions', []));
            $role->options()->sync($request->input('options', []));
        });
        $menu->flush();

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role, MenuService $menu): RedirectResponse
    {
        if ($role->name === 'Super Administrador') {
            return back()->withErrors(['role' => 'El rol Super Administrador está protegido.']);
        }
        $role->delete();
        $menu->flush();

        return back()->with('success', 'Rol eliminado.');
    }

    private function formData(): array
    {
        return ['permissions' => Permission::orderBy('name')->get(), 'options' => Option::orderBy('sort_order')->get()];
    }
}
