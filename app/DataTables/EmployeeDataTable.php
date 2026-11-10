<?php

namespace App\DataTables;

use App\Helpers\ImageHelper;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class EmployeeDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->addColumn('photo', function ($row) {
                $url = ImageHelper::publicUrl($row->image, 'employees');
                if ($url) {
                    return '<img src="'.e($url).'" alt="" class="emp-list-avatar">';
                }

                $parts = preg_split('/\s+/u', trim((string) $row->name)) ?: [];
                $initials = '';
                foreach (array_slice($parts, 0, 2) as $part) {
                    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
                }

                return '<span class="emp-list-avatar emp-list-fallback">'.e($initials !== '' ? $initials : '?').'</span>';
            })

            ->addColumn('department_name', function ($row) {
                return $row->department ? $row->department->name : '-';
            })

            ->addColumn('branch_name', function ($row) {
                return $row->branch ? $row->branch->name : '-';
            })

            ->addColumn('role_name', function ($row) {
                return $row->user?->roles?->pluck('name')->implode(', ') ?: '-';
            })

            ->editColumn('salary', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->salary);
            })

            ->editColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }

                return '<span class="badge badge-danger">Inactive</span>';
            })

            ->addColumn('action', function ($row) {
                $user = auth()->user();
                $buttons = '';

                if ($user?->can('update_employees')) {
                    $buttons .= '
                        <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="editEmployee('.$row->id.')">
                            <i class="fas fa-edit"></i>
                        </button>';
                }

                $isSelf = $row->user_id && (int) $row->user_id === (int) auth()->id();
                if ($user?->can('delete_employees') && ! $isSelf) {
                    $deleteUrl = route('employees.destroy', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $buttons .= '
                        <form action="'.$deleteUrl.'" method="POST" style="display:inline;" class="delete-form">
                            '.$csrf.' '.$method.'
                            <button type="button" class="btn btn-danger btn-icon btn-sm ms-1 delete-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>';
                }

                return $buttons !== ''
                    ? '<div class="text-center">'.$buttons.'</div>'
                    : '<span class="text-muted">-</span>';
            })

            ->rawColumns(['photo', 'status', 'action']);
    }

    public function query(Employee $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['department', 'branch', 'user.roles'])
            ->select('employees.*')
            ->withSum('commissions', 'earned_amount')
            ->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('employee-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white fw-bold', 'text' => '<i class="fas fa-file-excel me-1"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white fw-bold', 'text' => '<i class="fas fa-file-pdf me-1"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info btn-sm text-white fw-bold', 'text' => '<i class="fas fa-print me-1"></i> '.__('Print')],
                ],
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Employees...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::computed('photo')->title(__('Photo'))->exportable(false)->printable(false)->searchable(false)->orderable(false)->width(56)->addClass('text-center'),
            Column::make('name')->title(__('Name')),
            Column::make('email')->title(__('Email')),
            Column::make('phone')->title(__('Phone')),
            Column::make('department_name')->title(__('Department'))->name('department.name'),
            Column::make('branch_name')->title(__('Branch'))->name('branch.name'),
            Column::make('role_name')->title(__('Role'))->orderable(false)->searchable(false),
            Column::make('salary')->title(__('Salary')),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Employee_'.date('YmdHis');
    }
}
