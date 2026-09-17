@php
    $status = match ($user->status) {
        'active' => ['Activo', 'text-bg-success'],
        'suspended' => ['Suspendido', 'text-bg-danger'],
        'invited' => ['Invitado', 'text-bg-warning'],
        default => [$user->status, 'text-bg-secondary'],
    };
@endphp
<span class="badge {{ $status[1] }}">{{ $status[0] }}</span>
