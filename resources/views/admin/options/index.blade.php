@extends('layouts.app')

@section('title', 'Opciones del menú')
@section('page-title', 'Opciones del menú')
@section('page-description', 'Organice la navegación visible para cada rol o usuario')

@section('page-action')
    <a class="btn btn-primary" href="{{ route('admin.options.create') }}">
        <i class="bi bi-plus-lg"></i> Nueva opción
    </a>
@endsection

@section('content')
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Mostrar una opción no concede acceso: asigne también el permiso correspondiente.
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                {{ $dataTable->table(['class' => 'table table-hover w-100']) }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
