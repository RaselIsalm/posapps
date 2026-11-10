<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $table = (new EloquentDataTable($query))
            ->addIndexColumn()

            ->addColumn('contacts', function ($row) {
                return ($row->email ?? 'N/A').'<br>'.($row->phone ?? 'N/A');
            })

            ->addColumn('role', function ($row) {
                return $row->roles->pluck('name')->implode(', ') ?: 'N/A';
            })

            ->addColumn('status', function ($row) {
                return $row->is_active
                    ? '<span class="badge user-status-active">Active</span>'
                    : '<span class="badge user-status-inactive">Inactive</span>';
            })

            ->addColumn('action', function ($row) {
                $isSuperAdmin = auth()->user()->hasRole('Super-Admin');
                $targetIsSuperAdmin = $row->hasRole('Super-Admin');

                if (! $targetIsSuperAdmin || ($isSuperAdmin && $targetIsSuperAdmin)) {

                    $editUrl = route('users.edit', $row->id);
                    $deleteUrl = route('users.destroy', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    $deleteBtn = '';

                    if (! $row->hasRole('Super-Admin') && $row->id !== auth()->id()) {
                        if ($row->is_active) {
                            $previewUrl = route('users.removalPreview', $row->id);
                            $deleteBtn = '
                            <a class="dropdown-item text-danger user-remove-btn" href="javascript:void(0)" data-url="'.$deleteUrl.'" data-preview="'.$previewUrl.'">
                                <i class="fa-solid fa-user-slash me-1"></i> Remove
                            </a>
                        ';
                        } else {
                            $activateUrl = route('users.activate', $row->id);
                            $deleteBtn = '
                            <a class="dropdown-item activate-btn" href="javascript:void(0)" data-url="'.$activateUrl.'">
                                <i class="fa-solid fa-user-check me-1"></i> Activate
                            </a>
                        ';
                        }
                    }

                    return '
                        <div class="dropdown-custom text-center">
                            <i style="font-size: 1.5rem; cursor: pointer;" class="fas fa-ellipsis dropdown-toggle-custom" data-bs-toggle="dropdown" aria-expanded="false"></i>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                <li>
                                    <a class="dropdown-item" href="'.$editUrl.'">
                                        <i class="fa-solid fa-edit me-1"></i> Edit
                                    </a>
                                </li>
                                <li>'.$deleteBtn.'</li>
                            </ul>
                        </div>
                    ';
                }

                return '<span class="text-muted text-center d-block"><i class="fas fa-lock"></i></span>';
            })

            ->setRowId(fn ($row) => 'user-row-'.$row->id)
            ->rawColumns(['contacts', 'status', 'action']);

        $highlight = (int) request('highlight');
        if ($highlight > 0) {
            $table->order(function ($query) use ($highlight) {
                $query->reorder()
                    ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$highlight])
                    ->orderByDesc('id');
            });
        }

        return $table;
    }

    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()->with('roles')->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('user-table')
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
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Users...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::make('name')->title(__('Name')),
            Column::computed('contacts')->title(__('Contacts')),
            Column::make('role')->title(__('Role'))->orderable(false)->searchable(false),
            Column::computed('status')->title(__('Status'))->orderable(false)->searchable(false)->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'User_'.date('YmdHis');
    }
}
