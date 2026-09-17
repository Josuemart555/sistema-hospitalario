@extends('layouts.app')
@php($editing = isset($user))
@section('title', $editing ? 'Editar usuario' : 'Nuevo usuario')
@section('page-title', $editing ? 'Editar usuario' : 'Nuevo usuario')
@section('page-description', 'Complete únicamente la información necesaria')

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" enctype="multipart/form-data">
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-12 col-xxl-7">
                <div class="card user-form-card border-0 shadow-sm mb-0">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-4">Datos de la cuenta</h2>

                        <div class="mb-3">
                            <label class="form-label" for="name">Nombre completo</label>
                            <input class="form-control" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="email">Correo electrónico</label>
                            <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required>
                        </div>

                        @if($editing)
                            <div class="mb-3">
                                <label class="form-label" for="status">Estado</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" @selected(old('status', $user->status) === 'active')>Activo</option>
                                    <option value="invited" @selected(old('status', $user->status) === 'invited')>Invitado</option>
                                    <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspendido</option>
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="status" value="invited">
                            <div class="alert alert-info d-flex gap-2 align-items-start">
                                <i class="bi bi-envelope"></i>
                                <span>El usuario recibirá un enlace para crear su contraseña.</span>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label" for="avatar">Foto de perfil</label>
                            <input class="form-control" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp">
                        </div>

                        <div class="form-check form-switch">
                            <input type="hidden" name="two_factor_allowed" value="0">
                            <input class="form-check-input" id="two_factor_allowed" name="two_factor_allowed" type="checkbox" value="1" @checked(old('two_factor_allowed', $user->two_factor_allowed ?? false))>
                            <label class="form-check-label" for="two_factor_allowed">Permitir que este usuario active 2FA</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xxl-5">
                <div class="d-grid gap-4">
                    <div class="card user-form-card border-0 shadow-sm mb-0">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Roles</h2>
                            <div class="user-access-list">
                                @foreach($roles as $role)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role{{ $role->id }}" @checked(in_array($role->id, old('roles', $editing ? $user->roles->pluck('id')->all() : [])))>
                                        <label class="form-check-label" for="role{{ $role->id }}">{{ $role->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card user-form-card border-0 shadow-sm mb-0">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Permisos directos</h2>
                            <div class="user-access-list">
                                @foreach($permissions as $permission)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission{{ $permission->id }}" @checked(in_array($permission->id, old('permissions', $editing ? $user->permissions->pluck('id')->all() : [])))>
                                        <label class="form-check-label" for="permission{{ $permission->id }}">{{ $permission->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card user-form-card border-0 shadow-sm mb-0">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Opciones directas del menú</h2>
                            <div class="user-access-list">
                                @foreach($options as $option)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="options[]" value="{{ $option->id }}" id="option{{ $option->id }}" @checked(in_array($option->id, old('options', $editing ? $user->options->pluck('id')->all() : [])))>
                                        <label class="form-check-label" for="option{{ $option->id }}">{{ $option->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
            <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i>Guardar usuario</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Cancelar</a>
        </div>
    </form>

    @if($editing && $user->status === 'invited')
        <form method="POST" action="{{ route('admin.users.resend-invitation', $user) }}" class="mt-3">
            @csrf
            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-envelope"></i>Reenviar invitación</button>
        </form>
    @endif

    @if($editing)
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-3" onsubmit="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Eliminar usuario</button>
        </form>
    @endif
@endsection
