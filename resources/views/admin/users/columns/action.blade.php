<div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}">
        <i class="bi bi-pencil"></i>
        <span class="d-none d-sm-inline">Editar</span>
    </a>
    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.')">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-outline-danger" type="submit">
            <i class="bi bi-trash"></i>
            <span class="d-none d-sm-inline">Eliminar</span>
        </button>
    </form>
</div>
