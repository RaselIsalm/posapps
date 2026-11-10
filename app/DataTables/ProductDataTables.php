<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductDataTables extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            // Checkbox
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox form-check-input" data-id="'.$row->id.'">';
            })

            // Image
            ->addColumn('image', function ($row) {
                $url = $row->thumbnail
                    ? asset('storage/products/'.$row->thumbnail)
                    : asset('backend/assets/images/preview_image.png');

                return '<div class="view-details" data-id="'.$row->id.'" style="width: 50px; height: 50px; overflow: hidden; border-radius: 8px; border: 1px solid #eee; cursor: pointer;">
                            <img src="'.$url.'" alt="img" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>';
            })

            // Product Name
            ->editColumn('product_title', function ($row) {
                // IMEI Badge Logic
                $imeiBadge = $row->has_imei
                    ? '<span class="badge bg-dark ms-1" style="font-size: 10px;">IMEI</span>'
                    : '';

                return '
                    <div class="d-flex flex-column">
                        <a href="javascript:void(0)" class="fw-bold text-dark view-details" data-id="'.$row->id.'" style="text-decoration:none;">
                            '.$row->product_title.' '.$imeiBadge.'
                        </a>
                        <small class="text-muted">Code: <span class="text-primary">'.$row->product_code.'</span></small>
                    </div>';
            })

            // Category
            ->addColumn('category', function ($row) {
                $cat = $row->category ? $row->category->name : 'N/A';

                return '<span class="badge badge-soft-info text-black fw-bold">'.$cat.'</span>';
            })
            ->addColumn('brand', function ($row) {
                return $row->brand ? $row->brand->name : 'N/A';
            })

            // Quantity / Stock
            ->editColumn('quantity', function ($row) {
                $qty = $row->productBranches->sum('qty');
                $unit = $row->unit ? $row->unit->name : '';
                $alert = $row->alert_quantity ?? 5;

                if ($qty <= 0) {
                    return '<span class="badge bg-danger">Out of Stock</span>';
                } elseif ($qty <= $alert) {
                    return '<span class="badge bg-warning text-dark">'.$qty.' '.$unit.' (Low)</span>';
                }

                return '<span class="badge bg-success">'.$qty.' '.$unit.'</span>';
            })

            // Price & Cost
            ->editColumn('cost', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->product_cost);
            })
            ->editColumn('price', function ($row) {
                return '<span class="fw-bold text-dark">'.\App\Helpers\CurrencyHelper::format($row->selling_price).'</span>';
            })

            // Status
            ->addColumn('status', function ($row) {
                if ($row->is_active) {
                    return '<span class="badge bg-success">Active</span>';
                }

                return '<span class="badge bg-danger">Inactive</span>';
            })

            // Action Buttons
            ->addColumn('action', function ($row) {
                $editUrl = route('products.edit', $row->id);
                $deleteUrl = route('products.destroy', $row->id);

                return '
                    <div class="dropdown text-center">
                        <button class="btn btn-light btn-sm btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid #eee;">
                            <i class="fas fa-ellipsis text-black fw-bold"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item fw-bold text-black" href="'.$editUrl.'"><i class="far fa-edit me-2 text-primary fw-bold"></i> Edit</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item text-danger delete-btn fw-bold" data-url="'.$deleteUrl.'">
                                    <i class="far fa-trash-alt me-2 fw-bold"></i> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                ';
            })

            // Filter Logic
            ->filter(function ($instance) {
                if (! empty(request('search')['value'])) {
                    $instance->where(function ($w) {
                        $keyword = request('search')['value'];

                        $w->where('products.product_title', 'like', "%{$keyword}%")
                            ->orWhere('products.product_code', 'like', "%{$keyword}%")
                            ->orWhereHas('category', function ($q) use ($keyword) {
                                $q->where('name', 'like', "%{$keyword}%");
                            })
                            ->orWhereHas('brand', function ($q) use ($keyword) {
                                $q->where('name', 'like', "%{$keyword}%");
                            });
                    });
                }
            })
            ->setRowClass('clickable-row')
            ->setRowAttr([
                'data-id' => function ($row) {
                    return $row->id;
                },
                'style' => 'cursor: pointer;',
            ])

            ->rawColumns(['checkbox', 'image', 'product_title', 'category', 'quantity', 'price', 'status', 'action']);
    }

    public function query(Product $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['category', 'brand', 'unit', 'productBranches'])
            ->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('products-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(2)
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white', 'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info btn-sm text-white', 'text' => '<i class="fas fa-print"></i> '.__('Print')],
                ],
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Product...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('checkbox')
                ->title('<input type="checkbox" id="select-all">')
                ->exportable(false)
                ->printable(false)
                ->width(30)
                ->addClass('text-center'),

            Column::computed('image')->title(__('Image'))->width(60),
            Column::make('product_title')->title(__('Product Info')),
            Column::computed('category')->title(__('Category')),
            Column::computed('brand')->title(__('Brand')),
            Column::make('quantity')->title(__('Stock'))->addClass('text-center'),
            Column::make('cost')->title(__('Cost'))->addClass('text-center'),
            Column::make('price')->title(__('Price'))->addClass('text-center'),
            Column::computed('status')->title(__('Status'))->addClass('text-center')->width(90),

            Column::computed('action')
                ->title(__('Action'))
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
        ];
    }
}
