<?php

namespace App\DataTables;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BranchDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->editColumn('name', function ($row) {
                $url = $row->branch_logo ? asset('storage/branch/'.$row->branch_logo) : asset('backend/assets/images/preview_image.png');

                return '
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <img src="'.$url.'" alt="..." class="avatar-img rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                        </div>
                        <div>
                            <h6 class="mb-0 text-primary">'.$row->name.'</h6>
                        </div>
                    </div>';
            })

            // Contacts
            ->addColumn('contacts', function ($row) {
                $lines = [];
                if ($row->email) {
                    $lines[] = '<i class="fas fa-envelope text-muted me-1"></i> '.e($row->email);
                }
                if ($row->phone) {
                    $lines[] = '<i class="fas fa-mobile-alt text-muted me-1"></i> '.e($row->phone);
                }
                if ($row->telephone) {
                    $lines[] = '<i class="fas fa-phone text-muted me-1"></i> '.e($row->telephone);
                }
                if ($row->bin_number) {
                    $lines[] = '<span class="text-muted">BIN:</span> '.e($row->bin_number);
                }

                return '<div>'.(count($lines) ? implode('<br>', $lines) : '<span class="text-muted">â€”</span>').'</div>';
            })

            // Manager Relation
            ->addColumn('manager_name', function ($row) {
                return $row->manager ? $row->manager->name : '<span class="text-danger">N/A</span>';
            })

            // Status Badge
            ->editColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }

                return '<span class="badge badge-danger">Inactive</span>';
            })

            ->addColumn('action', function ($row) {
                $editUrl = route('branches.edit', $row->id);
                $deleteUrl = route('branches.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                <div class="d-flex justify-content-center">
                    <a href="'.$editUrl.'" 
                       class="btn btn-primary d-inline-flex align-items-center justify-content-center" 
                       style="width: 32px; height: 32px; padding: 0;" 
                       title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>

                    <button type="button" class="btn btn-danger d-inline-flex align-items-center justify-content-center delete-btn ms-1" style="width: 32px; height: 32px; padding: 0;" title="Delete" data-url="'.$deleteUrl.'">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>';
            })

            ->rawColumns(['name', 'contacts', 'manager_name', 'status', 'action']);
    }

    public function query(Branch $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['manager'])
            ->select('branches.*')
            ->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('branch-table')
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
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Branch...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::make('name')->title(__('Branch Name')),
            Column::make('address')->title(__('Location')),
            Column::computed('contacts')->title(__('Contacts')),
            Column::make('manager_name')->title(__('Manager'))->name('manager.name'),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Branch_'.date('YmdHis');
    }
}
