@extends('layouts.app')
@php($editing = isset($user))
@section('title', $editing ? 'Editar usuario' : 'Nuevo usuario')
@section('page-title', $editing ? 'Editar usuario' : 'Nuevo usuario')
@section('page-description', 'Complete únicamente la información necesaria')
@section('page-action')
    <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">
        <i class="bi bi-arrow-left"></i> Regresar
    </a>
@endsection

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" enctype="multipart/form-data">
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="card user-form-card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-4">Datos de la cuenta</h2>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Nombre completo</label>
                        <input class="form-control" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="email">Correo electrónico</label>
                        <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required>
                    </div>

                    @if($editing)
                        <div class="col-md-6">
                            <label class="form-label" for="status">Estado</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" @selected(old('status', $user->status) === 'active')>Activo</option>
                                <option value="invited" @selected(old('status', $user->status) === 'invited')>Invitado</option>
                                <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspendido</option>
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="status" value="invited">
                        <div class="col-12">
                            <div class="alert alert-info d-flex gap-2 align-items-start mb-0">
                                <i class="bi bi-envelope"></i>
                                <span>El usuario recibirá un enlace para crear su contraseña.</span>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <label class="form-label" for="avatar">Foto de perfil</label>
                        <input class="form-control" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp">
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="two_factor_allowed" value="0">
                            <input class="form-check-input" id="two_factor_allowed" name="two_factor_allowed" type="checkbox" value="1" @checked(old('two_factor_allowed', $user->two_factor_allowed ?? false))>
                            <label class="form-check-label" for="two_factor_allowed">Permitir que este usuario active 2FA</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card user-form-card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Roles</h2>
                <div data-vue-component="DualListbox" data-props="{{ json_encode([
                    'items' => $roles,
                    'selected' => old('roles', $editing ? $user->roles->pluck('id')->all() : []),
                    'name' => 'roles[]',
                    'leftLabel' => 'Disponibles',
                    'rightLabel' => 'Asignados',
                ]) }}"></div>
            </div>
        </div>

        <div class="card user-form-card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Permisos directos</h2>
                <div data-vue-component="DualListbox" data-props="{{ json_encode([
                    'items' => $permissions,
                    'selected' => old('permissions', $editing ? $user->permissions->pluck('id')->all() : []),
                    'name' => 'permissions[]',
                    'leftLabel' => 'Disponibles',
                    'rightLabel' => 'Asignados',
                ]) }}"></div>
            </div>
        </div>

        <div class="card user-form-card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Opciones directas del menú</h2>
                <div data-vue-component="OptionsTree" data-props="{{ json_encode([
                    'nodes' => $options,
                    'selected' => old('options', $editing ? $user->options->pluck('id')->all() : []),
                    'name' => 'options[]',
                ]) }}"></div>
            </div>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 mt-4 justify-content-sm-end">
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
@endsection
