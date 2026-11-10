<?php

namespace App\DataTables;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BrandDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            // Image Column
            ->addColumn('image', function ($row) {
                $url = $row->image ? asset('storage/brand/'.$row->image) : asset('backend/assets/images/preview_image.png');

                return '<img src="'.$url.'" class="img-fluid rounded" alt="Image" style="max-height: 50px; max-width: 50px;">';
            })

            // Status Badge
            ->editColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }

                return '<span class="badge badge-danger">Inactive</span>';
            })

            // Action Buttons
            ->addColumn('action', function ($row) {
                $deleteUrl = route('brands.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="text-center">
                        <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="editBrand('.$row->id.')">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button type="button" class="btn btn-danger btn-icon btn-sm ms-1 delete-btn" data-url="'.$deleteUrl.'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['image', 'status', 'action']);
    }

    public function query(Brand $model): QueryBuilder
    {
        return $model->newQuery()->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('brand-table')
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
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Brand...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::computed('image')->title(__('Image'))->width(60)->addClass('text-center'),
            Column::make('name')->title(__('Name')),
            Column::make('description')->title(__('Description')),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Brand_'.date('YmdHis');
    }
}
