<?php

namespace App\DataTables;

use App\Models\LeadNote;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LeadNoteDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->addColumn('lead_name', function ($row) {
                $name = e($row->lead->name ?? 'Unknown Lead');
                $number = e($row->lead->number ?? '');

                return '<span class="fw-bold text-primary">'.$name.'</span><br><small class="text-muted">'.$number.'</small>';
            })

            ->editColumn('notes', function ($row) {
                $escapedNote = e($row->notes);
                return '<span title="'.$escapedNote.'">'.Str::limit($escapedNote, 80).'</span>';
            })

            ->editColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }

                return '<span class="badge badge-danger">Inactive</span>';
            })

            ->addColumn('action', function ($row) {
                $deleteUrl = route('lead_notes.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="text-center">
                        <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="editLeadNote('.$row->id.')">
                            <i class="fas fa-edit"></i>
                        </button>

                        <form action="'.$deleteUrl.'" method="POST" style="display:inline;" class="delete-form">
                            '.$csrf.' '.$method.'
                            <button type="button" class="btn btn-danger btn-icon btn-sm ms-1 delete-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })

            ->rawColumns(['lead_name', 'notes', 'status', 'action']);
    }

    public function query(LeadNote $model): QueryBuilder
    {
        return $model->newQuery()
            ->with('lead')
            ->select('lead_notes.*')
            ->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('leadnote-table')
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
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Notes...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::make('lead_name')->title(__('Lead Name'))->name('lead.name'),
            Column::make('notes')->title(__('Note Details')),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'LeadNote_'.date('YmdHis');
    }
}
