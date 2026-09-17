<div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.options.edit', $option) }}">
        <i class="bi bi-pencil"></i> Editar
    </a>
    <form method="POST" action="{{ route('admin.options.destroy', $option) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta opción?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-outline-danger" type="submit">
            <i class="bi bi-trash"></i> Eliminar
        </button>
    </form>
</div>
