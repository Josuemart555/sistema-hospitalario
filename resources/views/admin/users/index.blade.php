@extends('layouts.app')

@section('title', 'Usuarios')
@section('page-title', 'Usuarios')
@section('page-description', 'Administre las cuentas y sus accesos.')

@section('page-action')
    <a class="btn btn-primary" href="{{ route('admin.users.create') }}">
        <i class="bi bi-plus-lg"></i> Nuevo usuario
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
