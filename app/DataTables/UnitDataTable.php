<?php

namespace App\DataTables;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UnitDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->addColumn('base_unit_name', function ($row) {
                return $row->baseUnit ? $row->baseUnit->name : '<span class="text-muted">Base</span>';
            })

            ->addColumn('operation_value', function ($row) {
                if ($row->baseUnit) {
                    return $row->operator.' '.$row->conversion_rate;
                }

                return '-';
            })

            ->editColumn('is_active', function ($row) {
                if ($row->is_active == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }

                return '<span class="badge badge-danger">Inactive</span>';
            })

            ->addColumn('action', function ($row) {
                $deleteUrl = route('units.destroy', $row->id);

                return '
                    <div class="text-center">
                        <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="editUnit('.$row->id.')">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button type="button" class="btn btn-danger btn-icon btn-sm ms-1 delete-btn" data-url="'.$deleteUrl.'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['base_unit_name', 'is_active', 'action']);
    }

    public function query(Unit $model): QueryBuilder
    {
        return $model->newQuery()
            ->with('baseUnit')
            ->select('units.*')
            ->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('unit-table')
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
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Unit...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::make('name')->title(__('Name')),
            Column::make('short_code')->title(__('Short Code')),
            Column::make('base_unit_name')->title(__('Base Unit'))->name('baseUnit.name'),
            Column::make('operation_value')->title(__('Operation'))->searchable(false),
            Column::make('is_active')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Unit_'.date('YmdHis');
    }
}
