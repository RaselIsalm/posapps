<?php

namespace App\DataTables;

use App\Models\Payroll;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PayrollDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->addColumn('employee_info', function ($row) {
                $name = $row->employee ? e($row->employee->name) : 'Unknown';
                $phone = $row->employee ? e($row->employee->phone) : '';

                return '<strong>'.$name.'</strong><br><small class="text-muted">'.$phone.'</small>';
            })

            ->addColumn('month_year', function ($row) {
                return $row->salary_month.', '.$row->salary_year;
            })

            ->editColumn('net_salary', function ($row) {
                return '<strong>'.\App\Helpers\CurrencyHelper::format($row->net_salary).'</strong>';
            })

            ->editColumn('status', function ($row) {
                if ($row->status == 'paid') {
                    return '<span class="badge badge-light-success">Paid</span>';
                } elseif ($row->status == 'pending') {
                    return '<span class="badge badge-light-warning">Pending</span>';
                } else {
                    return '<span class="badge badge-light-danger">Cancelled</span>';
                }
            })

            ->addColumn('action', function ($row) {
                $user = auth()->user();
                $buttons = '';

                if ($user?->can('update_payrolls')) {
                    $buttons .= '
                        <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="editPayroll('.$row->id.')">
                            <i class="fas fa-edit"></i>
                        </button>';
                }

                if ($user?->can('delete_payrolls')) {
                    $deleteUrl = route('payrolls.destroy', $row->id);
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

            ->rawColumns(['employee_info', 'net_salary', 'status', 'action']);
    }

    public function query(Payroll $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['employee'])
            ->select('payrolls.*')
            ->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('payroll-table')
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
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Payroll...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::computed('employee_info')->title(__('Employee')),
            Column::computed('month_year')->title(__('Month/Year')),
            Column::make('net_salary')->title(__('Net Salary')),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Payroll_'.date('YmdHis');
    }
}
