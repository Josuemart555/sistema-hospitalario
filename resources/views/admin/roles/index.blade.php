@extends('layouts.app')

@section('title', 'Roles')
@section('page-title', 'Roles')
@section('page-description', 'Agrupe permisos y opciones por responsabilidad')

@section('page-action')
    <a class="btn btn-primary" href="{{ route('admin.roles.create') }}">
        <i class="bi bi-plus-lg"></i> Nuevo rol
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
