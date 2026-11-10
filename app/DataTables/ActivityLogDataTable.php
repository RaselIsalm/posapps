<?php

namespace App\DataTables;

use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\Request;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ActivityLogDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('causer', function ($row) {
                if ($row->causer) {
                    return $row->causer->name.' <br><small class="text-muted">'.$row->causer->email.'</small>';
                }

                return '<span class="badge bg-secondary">System</span>';
            })
            ->addColumn('log_name', function ($row) {
                $colors = [
                    'auth' => 'primary',
                    'security' => 'danger',
                    'sales' => 'success',
                    'default' => 'info',
                ];
                $color = $colors[$row->log_name] ?? $colors['default'];

                return '<span class="badge bg-'.$color.'">'.ucfirst($row->log_name).'</span>';
            })
            ->editColumn('description', function ($row) {
                return ucfirst($row->description);
            })
            ->editColumn('created_at', function ($row) {
                $timeAgo = $row->created_at->diffForHumans();
                $exactTime = $row->created_at->format('d M Y, h:i A');

                return $exactTime.'<br><small class="text-muted">'.$timeAgo.'</small>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="javascript:void(0)" data-url="'.route('activity-logs.show', $row->id).'" class="btn btn-sm btn-info text-white view-log-btn" title="View Details">
                            <i class="fas fa-eye"></i> View
                        </a>';

                return $btn;
            })
            ->rawColumns(['causer', 'log_name', 'created_at', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Activity $model, Request $request)
    {
        $query = $model->newQuery()->with('causer')->latest();

        if ($request->has('log_name') && ! empty($request->log_name)) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->has('user_id') && ! empty($request->user_id)) {
            $query->where('causer_id', $request->user_id)
                ->where('causer_type', 'App\Models\User');
        }

        if ($request->has('subject_type') && ! empty($request->subject_type)) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->has('start_date') && ! empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && ! empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('activity-log-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1, 'desc')
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white', 'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info btn-sm text-white', 'text' => '<i class="fas fa-print"></i> '.__('Print')],
                ],
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Logs...')],
                'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns()
    {
        return [
            Column::make('DT_RowIndex')->title(__('SL'))->searchable(false)->orderable(false),
            Column::make('created_at')->title(__('Date & Time')),
            Column::make('log_name')->title(__('Module')),
            Column::make('causer')->title(__('Performed By'))->searchable(false),
            Column::make('description')->title(__('Action')),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(80)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ActivityLog_'.date('YmdHis');
    }
}
