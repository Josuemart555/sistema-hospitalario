{{ $user->roles->pluck('name')->join(', ') ?: 'Sin rol' }}
