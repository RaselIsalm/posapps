<?php

namespace App\DataTables;

use App\Models\Expenses;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ExpensesDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            // Date Format
            ->editColumn('date', function ($row) {
                return date('d M, Y', strtotime($row->date));
            })

            // Relations
            ->addColumn('category_name', function ($row) {
                return $row->category ? $row->category->name : 'N/A';
            })
            ->addColumn('branch_name', function ($row) {
                return $row->branch ? $row->branch->name : 'N/A';
            })
            ->addColumn('account_name', function ($row) {
                return $row->account ? $row->account->name : 'N/A';
            })

            // Amount Format
            ->editColumn('amount', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->amount);
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
                $deleteUrl = route('expenses.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="text-center">
                        <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="editExpense('.$row->id.')">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button type="button" class="btn btn-danger btn-icon btn-sm ms-1 delete-btn" title="Delete" data-url="'.$deleteUrl.'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['status', 'action']);
    }

    public function query(Expenses $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['category', 'branch', 'account'])
            ->select('expenses.*')
            ->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('expenses-table')
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
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Expense...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::make('date')->title(__('Date')),
            Column::make('category_name')->title(__('Category'))->name('category.name'),
            Column::make('branch_name')->title(__('Branch'))->name('branch.name'),
            Column::make('account_name')->title(__('Account'))->name('account.name'),
            Column::make('amount')->title(__('Amount')),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Expenses_'.date('YmdHis');
    }
}
