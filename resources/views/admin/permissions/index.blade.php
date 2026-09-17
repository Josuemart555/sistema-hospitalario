@extends('layouts.app')

@section('title', 'Permisos')
@section('page-title', 'Permisos')
@section('page-description', 'Capacidades protegidas del sistema')

@section('page-action')
    <a class="btn btn-primary" href="{{ route('admin.permissions.create') }}">
        <i class="bi bi-plus-lg"></i> Nuevo permiso
    </a>
@endsection

@section('content')
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
