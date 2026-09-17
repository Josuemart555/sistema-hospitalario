@extends('layouts.app')
@php($editing = isset($role))
@section('title', $editing ? 'Editar rol' : 'Nuevo rol')
@section('page-title', $editing ? 'Editar rol' : 'Nuevo rol')
@section('page-description', 'Defina lo que podrán ver y hacer sus integrantes')
@section('page-action')
    <a class="btn btn-outline-secondary" href="{{ route('admin.roles.index') }}">
        <i class="bi bi-arrow-left"></i> Regresar
    </a>
@endsection

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="card user-form-card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="mb-0">
                    <label class="form-label" for="name">Nombre del rol</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name', $role->name ?? '') }}" required @readonly($editing && $role->name === 'Super Administrador')>
                </div>
            </div>
        </div>

        <div class="card user-form-card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-1">Permisos</h2>
                <p class="text-muted">Acciones que podrá realizar.</p>
                <div data-vue-component="DualListbox" data-props="{{ json_encode([
                    'items' => $permissions,
                    'selected' => old('permissions', $editing ? $role->permissions->pluck('id')->all() : []),
                    'name' => 'permissions[]',
                    'leftLabel' => 'Disponibles',
                    'rightLabel' => 'Asignados',
                ]) }}"></div>
            </div>
        </div>

        <div class="card user-form-card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-1">Opciones del menú</h2>
                <p class="text-muted">Los permisos también deben coincidir.</p>
                <div class="user-access-list">
                    @include('admin.options.partials.tree-checkboxes', ['nodes' => $options, 'selected' => old('options', $editing ? $role->options->pluck('id')->all() : []), 'depth' => 0])
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 mt-4 justify-content-sm-end">
            <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i>Guardar rol</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.roles.index') }}">Cancelar</a>
        </div>
    </form>
@endsection
