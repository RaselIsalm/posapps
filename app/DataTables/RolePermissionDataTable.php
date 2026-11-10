<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class RolePermissionDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->editColumn('description', function ($row) {
                return $row->description ?? 'N/A';
            })

            ->addColumn('action', function ($row) {

                if ($row->name === 'Super-Admin' || $row->name === 'Admin') {
                    return '<span class="badge badge-secondary"><i class="fas fa-lock me-1"></i> Protected</span>';
                }

                $editUrl = route('roles.edit', $row->id);
                $permissionUrl = route('roles.managePermissions', $row->id);
                $deleteUrl = route('roles.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="dropdown-custom text-center">
                        <i style="font-size: 1.5rem; cursor: pointer;" class="fas fa-ellipsis dropdown-toggle-custom" data-bs-toggle="dropdown" aria-expanded="false"></i>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                            
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="editRole('.$row->id.')">
                                    <i class="fa-solid fa-edit me-1"></i> Edit
                                </a>
                            </li>
                            
                            <li>
                                <a class="dropdown-item" href="'.$permissionUrl.'">
                                    <i class="fa-solid fa-key me-1"></i> Permissions
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item text-danger delete-btn" href="javascript:void(0)" data-url="'.$deleteUrl.'">
                                    <i class="fa-solid fa-trash me-1"></i> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                ';
            })

            ->rawColumns(['action']);
    }

    public function query(Role $model): QueryBuilder
    {
        return $model->newQuery();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('rolepermission-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white', 'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info btn-sm text-white', 'text' => '<i class="fas fa-print"></i> '.__('Print')],
                ],
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Role...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::make('name')->title(__('Role Name')),
            Column::make('description')->title(__('Description')),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'RolePermission_'.date('YmdHis');
    }
}
