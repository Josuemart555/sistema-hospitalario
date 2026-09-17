<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class UsersDataTable extends AdministrationDataTable
{
    /**
     * @param  QueryBuilder<User>  $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', fn (User $user): string => trim(view('admin.users.columns.user', compact('user'))->render()))
            ->editColumn('status', fn (User $user): string => trim(view('admin.users.columns.status', compact('user'))->render()))
            ->addColumn('roles', fn (User $user): string => trim(view('admin.users.columns.roles', compact('user'))->render()))
            ->addColumn('action', fn (User $user): string => trim(view('admin.users.columns.action', compact('user'))->render()))
            ->filterColumn('name', function (QueryBuilder $query, string $keyword): void {
                $query->where(function (QueryBuilder $query) use ($keyword): void {
                    $query->where('users.name', 'like', "%{$keyword}%")
                        ->orWhere('users.email', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['name', 'status', 'roles', 'action'])
            ->setRowId('id');
    }

    /**
     * @return QueryBuilder<User>
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()
            ->select(['users.id', 'users.name', 'users.email', 'users.status'])
            ->with('roles:id,name');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('users-table')
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
            Column::make('name')->title('Usuario'),
            Column::make('status')->title('Estado')->addClass('d-none d-md-table-cell'),
            Column::make('roles')->name('roles.name')->title('Roles')->orderable(false)->addClass('d-none d-lg-table-cell'),
            Column::computed('action')->title('Acciones')->exportable(false)->printable(false)->orderable(false)->searchable(false)->addClass('text-end'),
        ];
    }
}
