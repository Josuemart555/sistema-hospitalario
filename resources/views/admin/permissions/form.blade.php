@extends('layouts.app')
@php($editing = isset($permission))
@section('title', $editing ? 'Editar permiso' : 'Nuevo permiso')
@section('page-title', $editing ? 'Editar permiso' : 'Nuevo permiso')
@section('page-description', 'Use una clave corta, estable y descriptiva')
@section('page-action')
    <a class="btn btn-outline-secondary" href="{{ route('admin.permissions.index') }}">
        <i class="bi bi-arrow-left"></i> Regresar
    </a>
@endsection

@section('content')
    <div class="card user-form-card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ $editing ? route('admin.permissions.update', $permission) : route('admin.permissions.store') }}">
                @csrf
                @if($editing)
                    @method('PUT')
                @endif

                <label class="form-label" for="name">Clave del permiso</label>
                <input class="form-control" id="name" name="name" value="{{ old('name', $permission->name ?? '') }}" placeholder="modulo.accion" required>
                <small class="text-muted">Solo minúsculas, números, puntos y guiones.</small>

                <div class="d-flex flex-column flex-sm-row gap-2 mt-4 justify-content-sm-end">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i>Guardar permiso</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.permissions.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
