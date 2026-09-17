<?php

namespace App\DataTables;

use App\Models\Option;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class OptionsDataTable extends AdministrationDataTable
{
    /**
     * @param  QueryBuilder<Option>  $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', fn (Option $option): string => trim(view('admin.options.columns.name', compact('option'))->render()))
            ->editColumn('route_name', fn (Option $option): string => trim(view('admin.options.columns.route', compact('option'))->render()))
            ->addColumn('permission', fn (Option $option): string => $option->permission?->name ?? 'Sin permiso')
            ->editColumn('is_active', fn (Option $option): string => trim(view('admin.options.columns.status', compact('option'))->render()))
            ->addColumn('action', fn (Option $option): string => trim(view('admin.options.columns.action', compact('option'))->render()))
            ->rawColumns(['name', 'route_name', 'is_active', 'action'])
            ->setRowId('id');
    }

    /**
     * @return QueryBuilder<Option>
     */
    public function query(Option $model): QueryBuilder
    {
        return $model->newQuery()
            ->select(['options.id', 'options.parent_id', 'options.permission_id', 'options.name', 'options.route_name', 'options.icon', 'options.sort_order', 'options.is_active'])
            ->with(['parent:id,name', 'permission:id,name']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('options-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(5)
            ->parameters($this->defaultParameters(20));
    }

    /**
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Opción'),
            Column::make('route_name')->title('Ruta'),
            Column::make('permission')->name('permission.name')->title('Permiso')->orderable(false),
            Column::make('is_active')->title('Estado'),
            Column::computed('action')->title('Acciones')->exportable(false)->printable(false)->orderable(false)->searchable(false)->addClass('text-end'),
            Column::make('sort_order')->title('Orden')->visible(false)->searchable(false),
        ];
    }
}
