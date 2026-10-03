<div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.roles.edit', $role) }}">
        <i class="bi bi-pencil"></i> Editar
    </a>
    @if($role->name !== 'Super Administrador')
        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este rol?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit">
                <i class="bi bi-trash"></i> Eliminar
            </button>
        </form>
    @endif
</div>
