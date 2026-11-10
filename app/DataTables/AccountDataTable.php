<?php

namespace App\DataTables;

use App\Models\Account;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AccountDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->editColumn('name', function ($row) {
                if (in_array(request('action'), ['excel', 'csv', 'pdf', 'print'], true)) {
                    return $row->name;
                }

                $html = '<span>'.e($row->name).'</span>';
                if ($row->is_default) {
                    $html .= ' <span class="badge badge-primary ms-1">Default</span>';
                }
                if ($row->allow_manual_amount) {
                    $html .= ' <span class="badge badge-warning ms-1">Enter amount</span>';
                }

                return $html;
            })

            ->editColumn('initial_balance', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->initial_balance);
            })
            ->editColumn('current_balance', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->current_balance ?? 0);
            })

            ->editColumn('is_active', function ($row) {
                if ($row->is_active) {
                    return '<span class="badge badge-success">Active</span>';
                }

                return '<span class="badge badge-danger">Inactive</span>';
            })

            ->addColumn('action', function ($row) {
                $deleteUrl = route('accounts.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="text-center">
                        <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="editAccount('.$row->id.')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button type="button" class="btn btn-danger btn-icon btn-sm ms-1 delete-btn" title="Delete" data-url="'.$deleteUrl.'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['name', 'is_active', 'action']);
    }

    public function query(Account $model): QueryBuilder
    {
        return $model->newQuery()->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('account-table')
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
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Account...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::make('name')->title(__('Name')),
            Column::make('account_number')->title(__('Account Number')),
            Column::make('initial_balance')->title(__('Initial Balance')),
            Column::make('current_balance')->title(__('Current Balance')),
            Column::make('is_active')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Account_'.date('YmdHis');
    }
}
