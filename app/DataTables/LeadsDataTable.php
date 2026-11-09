<?php

namespace App\DataTables;

use App\Models\Leads;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LeadsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->addColumn('latest_note', function ($row) {
                if ($row->notes->isNotEmpty()) {
                    $latestNote = $row->notes->sortByDesc('created_at')->first()->notes;

                    return '<span title="'.e($latestNote).'">'.e(Str::limit($latestNote, 40)).'</span>';
                }

                return '<span class="text-muted small">No notes yet</span>';
            })

            ->editColumn('next_follow_up_date', function ($row) {
                if (! $row->next_follow_up_date) {
                    return '<span class="text-muted small">Not Set</span>';
                }

                $date = Carbon::parse($row->next_follow_up_date);
                $isPast = $date->isPast() && ! $date->isToday();
                $class = $isPast ? 'text-danger fw-bold' : 'text-primary fw-bold';

                return '<span class="'.$class.'">'.$date->format('d M, Y').'</span>';
            })
            ->editColumn('status', function ($row) {
                if (! empty($row->customer_id)) {
                    return '<span class="badge badge-success" style="background-color: #146c43; color: white;">Converted</span>';
                }
                if ($row->status == 1) {
                    return '<span class="badge badge-info" style="background-color: #3b82f6; color: white;">Active</span>';
                }

                return '<span class="badge badge-secondary" style="background-color: #6b7280; color: white;">Inactive</span>';
            })

            ->addColumn('action', function ($row) {
                $deleteUrl = route('leads.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                $buttons = '<div class="text-center d-flex justify-content-center gap-1">';

                // If not converted, show Convert button
                if (empty($row->customer_id)) {
                    $convertUrl = route('leads.convert', $row->id);
                    $buttons .= '
                        <form action="'.$convertUrl.'" method="POST" style="display:inline;" class="convert-form">
                            '.$csrf.'
                            <button type="button" class="btn btn-success btn-icon btn-sm text-white convert-btn" title="Convert to Customer" data-url="'.$convertUrl.'">
                                <i class="fas fa-user-check"></i>
                            </button>
                        </form>';
                }

                $buttons .= '
                        <button type="button" class="btn btn-warning btn-icon btn-sm text-white" 
                            title="Add Note" onclick="addNote('.$row->id.')">
                            <i class="fas fa-sticky-note"></i>
                        </button>

                        <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="editLead('.$row->id.')">
                            <i class="fas fa-edit"></i>
                        </button>

                        <form action="'.$deleteUrl.'" method="POST" style="display:inline;" class="delete-form">
                            '.$csrf.' '.$method.'
                            <button type="button" class="btn btn-danger btn-icon btn-sm delete-btn" data-url="'.$deleteUrl.'">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                ';

                return $buttons;
            })

            ->rawColumns(['latest_note', 'next_follow_up_date', 'status', 'action']);
    }

    public function query(Leads $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['notes'])
            ->select('leads.*')
            ->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('leads-table')
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
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Leads...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::make('name')->title(__('Name')),
            Column::make('number')->title(__('Number')),
            Column::make('email')->title(__('Email')),
            Column::computed('latest_note')->title(__('Latest Note')),
            Column::make('next_follow_up_date')->title(__('Next Follow Up'))->addClass('text-center'),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(180)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Leads_'.date('YmdHis');
    }
}
