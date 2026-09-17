<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\UsersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Option;
use App\Models\User;
use App\Notifications\UserInvitationNotification;
use App\Services\MenuService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
        $user = DB::transaction(function () use ($request): User {
            $user = User::create([
                ...$request->safe()->only(['name', 'email']),
                'password' => Hash::make(Str::password(32)),
                'status' => 'invited',
                'two_factor_allowed' => $request->boolean('two_factor_allowed'),
            ]);
            $user->syncRoles($request->input('roles', []));
            $user->syncPermissions($request->input('permissions', []));
            $user->options()->sync($request->input('options', []));
            if ($request->hasFile('avatar')) {
                $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            }

            return $user;
        });
        $user->notify(new UserInvitationNotification(Password::broker()->createToken($user)));
        $menu->flush();

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado. La invitación fue enviada por correo.');
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
            $allowed = $request->boolean('two_factor_allowed');
            $user->update([...$request->safe()->only(['name', 'email', 'status']), 'two_factor_allowed' => $allowed]);
            if (! $allowed) {
                $user->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
            }
            $user->syncRoles($request->input('roles', []));
            $user->syncPermissions($request->input('permissions', []));
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

    public function resendInvitation(User $user): RedirectResponse
    {
        abort_unless($user->status === 'invited', 422);
        $user->notify(new UserInvitationNotification(Password::broker()->createToken($user)));

        return back()->with('success', 'Invitación reenviada.');
    }

    private function formData(): array
    {
        return [
            'roles' => Role::query()->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('name')->get(),
            'options' => Option::query()->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }
}
