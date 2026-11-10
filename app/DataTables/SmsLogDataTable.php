<?php

namespace App\DataTables;

use App\Models\SmsLog;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SmsLogDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('created_at', function ($row) {
                return '<span class="fw-bold text-dark">'.date('d M, Y', strtotime($row->created_at)).'</span><br><small class="text-muted">'.date('h:i A', strtotime($row->created_at)).'</small>';
            })
            ->editColumn('phone_number', function ($row) {
                return '<span class="fw-bold" style="color: '.\App\Helpers\ThemeHelper::getPrimaryColor().';">'.$row->phone_number.'</span>';
            })
            ->editColumn('message', function ($row) {
                return '<div style="white-space: normal; min-width: 250px;">'.$row->message.'</div>';
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'success') {
                    return '<span class="badge" style="background-color: #d1e7dd; color: #146c43; padding: 5px 10px; border-radius: 5px;"><i class="fas fa-check-circle me-1"></i> SUCCESS</span>';
                } elseif ($row->status == 'failed') {
                    return '<span class="badge" style="background-color: #f8d7da; color: #842029; padding: 5px 10px; border-radius: 5px;"><i class="fas fa-times-circle me-1"></i> FAILED</span>';
                }

                return '<span class="badge bg-warning text-dark">'.strtoupper($row->status).'</span>';
            })
            ->editColumn('response', function ($row) {
                return '<small class="text-muted" style="white-space: normal; max-width: 200px; display: inline-block;">'.$row->response.'</small>';
            })
            ->rawColumns(['created_at', 'phone_number', 'message', 'status', 'response']);
    }

    public function query(SmsLog $model)
    {
        return $model->newQuery()->latest();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('sms-log-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1, 'desc')
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white fw-bold', 'text' => '<i class="fas fa-file-excel me-1"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white fw-bold', 'text' => '<i class="fas fa-file-pdf me-1"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info btn-sm text-white fw-bold', 'text' => '<i class="fas fa-print me-1"></i> '.__('Print')],
                ],
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false),
            Column::make('created_at')->title(__('Date & Time'))->width(120),
            Column::make('phone_number')->title(__('Phone Number'))->width(150),
            Column::make('message')->title(__('SMS Content'))->width(300)->orderable(false),
            Column::make('status')->title(__('Status'))->className('text-center')->width(100),
            Column::make('response')->title(__('API Response'))->orderable(false),
        ];
    }
}
