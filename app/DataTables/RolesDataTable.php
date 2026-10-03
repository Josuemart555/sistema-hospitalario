<?php

namespace App\DataTables;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class RolesDataTable extends AdministrationDataTable
{
    /**
     * @param  QueryBuilder<Role>  $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', fn (Role $role): string => trim(view('admin.roles.columns.action', compact('role'))->render()))
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * @return QueryBuilder<Role>
     */
    public function query(Role $model): QueryBuilder
    {
        return $model->newQuery()
            ->select(['roles.id', 'roles.name'])
            ->withCount(['users', 'permissions']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('roles-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->parameters($this->defaultParameters(15));
    }

    /**
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Nombre')->addClass('fw-semibold'),
            Column::make('users_count')->title('Usuarios')->searchable(false),
            Column::make('permissions_count')->title('Permisos')->searchable(false),
            Column::computed('action')->title('Acciones')->exportable(false)->printable(false)->orderable(false)->searchable(false)->addClass('text-end'),
        ];
    }
}
