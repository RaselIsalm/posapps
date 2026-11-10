<?php

namespace App\DataTables;

use App\Models\EmployeeAttendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class EmployeeAttendanceDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->addColumn('employee_name', function ($row) {
                return $row->employee ? e($row->employee->name) : '<span class="text-danger">N/A</span>';
            })

            ->editColumn('attendance_date', function ($row) {
                return Carbon::parse($row->attendance_date)->format('Y-m-d');
            })

            ->editColumn('check_in_time', function ($row) {
                return $row->check_in_time ? Carbon::parse($row->check_in_time)->format('h:i A') : '-';
            })

            ->editColumn('check_out_time', function ($row) {
                return $row->check_out_time ? Carbon::parse($row->check_out_time)->format('h:i A') : '-';
            })

            ->editColumn('total_hours', function ($row) {
                if ($row->total_hours) {
                    $hours = floor($row->total_hours);
                    $minutes = round(($row->total_hours - $hours) * 60);

                    $timeString = '';
                    if ($hours > 0) {
                        $timeString .= $hours.' Hrs ';
                    }
                    if ($minutes > 0) {
                        $timeString .= $minutes.' Mins';
                    }
                    if (empty($timeString)) {
                        $timeString = '0 Mins';
                    }

                    return '<span class="fw-bold text-primary">'.trim($timeString).'</span>';
                }

                return '<span class="text-muted small">N/A</span>';
            })

            ->editColumn('note', function ($row) {
                $escapedNote = e($row->note);
                return '<span title="'.$escapedNote.'">'.Str::limit($escapedNote, 30).'</span>';
            })

            ->editColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-success">Present</span>';
                }

                return '<span class="badge badge-danger">Absent</span>';
            })

            ->addColumn('action', function ($row) {
                $user = auth()->user();
                $buttons = '';

                if ($user?->can('update_employees_attendances')) {
                    $buttons .= '
                        <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="editAttendance('.$row->id.')">
                            <i class="fas fa-edit"></i>
                        </button>';
                }

                if ($user?->can('delete_employees_attendances')) {
                    $deleteUrl = route('attendance.destroy', $row->id);
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

            ->rawColumns(['employee_name', 'total_hours', 'note', 'status', 'action']);
    }

    public function query(EmployeeAttendance $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['employee'])
            ->select($model->getTable().'.*')
            ->latest('attendance_date');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('employeeattendance-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(2)
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white fw-bold', 'text' => '<i class="fas fa-file-excel me-1"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white fw-bold', 'text' => '<i class="fas fa-file-pdf me-1"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info btn-sm text-white fw-bold', 'text' => '<i class="fas fa-print me-1"></i> '.__('Print')],
                ],
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Attendance...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::make('employee_name')->title(__('Employee'))->name('employee.name'),
            Column::make('attendance_date')->title(__('Date')),
            Column::make('check_in_time')->title(__('Check In')),
            Column::make('check_out_time')->title(__('Check Out')),
            Column::make('total_hours')->title(__('Total Hrs'))->addClass('text-center'),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::make('note')->title(__('Note')),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'EmployeeAttendance_'.date('YmdHis');
    }
}
