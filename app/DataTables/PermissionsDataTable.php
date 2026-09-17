<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class PermissionsDataTable extends AdministrationDataTable
{
    /**
     * @param  QueryBuilder<Permission>  $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', fn (Permission $permission): string => trim(view('admin.permissions.columns.name', compact('permission'))->render()))
            ->addColumn('action', fn (Permission $permission): string => trim(view('admin.permissions.columns.action', compact('permission'))->render()))
            ->rawColumns(['name', 'action'])
            ->setRowId('id');
    }

    /**
     * @return QueryBuilder<Permission>
     */
    public function query(Permission $model): QueryBuilder
    {
        return $model->newQuery()
            ->select(['permissions.id', 'permissions.name'])
            ->withCount('roles');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('permissions-table')
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
            Column::make('name')->title('Clave del permiso'),
            Column::make('roles_count')->title('Roles')->searchable(false),
            Column::computed('action')->title('Acciones')->exportable(false)->printable(false)->orderable(false)->searchable(false)->addClass('text-end'),
        ];
    }
}
