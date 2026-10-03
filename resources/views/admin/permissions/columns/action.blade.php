<div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.permissions.edit', $permission) }}">
        <i class="bi bi-pencil"></i> Editar
    </a>
    @if(!in_array($permission->name, config('administration.protected_permissions'), true))
        <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este permiso?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit">
                <i class="bi bi-trash"></i> Eliminar
            </button>
        </form>
    @endif
</div>
