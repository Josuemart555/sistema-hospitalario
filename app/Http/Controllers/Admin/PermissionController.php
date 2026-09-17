<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\PermissionsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Services\MenuService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(PermissionsDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('admin.permissions.index');
    }

    public function create(): View
    {
        return view('admin.permissions.form');
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        Permission::create(['name' => $request->string('name'), 'guard_name' => 'web']);

        return redirect()->route('admin.permissions.index')->with('success', 'Permiso creado correctamente.');
    }

    public function show(Permission $permission): RedirectResponse
    {
        return redirect()->route('admin.permissions.edit', $permission);
    }

    public function edit(Permission $permission): View
    {
        return view('admin.permissions.form', compact('permission'));
    }

    public function update(StorePermissionRequest $request, Permission $permission, MenuService $menu): RedirectResponse
    {
        $permission->update(['name' => $request->string('name')]);
        $menu->flush();

        return redirect()->route('admin.permissions.index')->with('success', 'Permiso actualizado correctamente.');
    }

    public function destroy(Permission $permission, MenuService $menu): RedirectResponse
    {
        if (in_array($permission->name, config('administration.protected_permissions'), true)) {
            return back()->withErrors(['permission' => 'Este permiso del sistema no puede eliminarse.']);
        }
        $permission->delete();
        $menu->flush();

        return back()->with('success', 'Permiso eliminado.');
    }
}
