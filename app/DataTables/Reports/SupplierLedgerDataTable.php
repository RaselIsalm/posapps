<?php

namespace App\DataTables\Reports;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SupplierLedgerDataTable extends DataTable
{
    public function dataTable($query)
    {
        $request = $this->request();
        $supplier_id = $request->get('supplier_id');
        $branch_id = $request->get('branch_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        $data = $this->getLedgerData($supplier_id, $branch_id, $start_date, $end_date);

        return datatables()
            ->collection($data)
            ->addColumn('date', function ($row) {
                return $row['date'];
            })
            ->addColumn('description', function ($row) {
                return $row['description'];
            })
            ->addColumn('reference_no', function ($row) {
                return $row['reference_no'];
            })
            ->addColumn('debit', function ($row) {
                return $row['debit'];
            })
            ->addColumn('credit', function ($row) {
                return $row['credit'];
            })
            ->addColumn('balance', function ($row) {
                return $row['balance'];
            })
            ->rawColumns(['description', 'balance', 'debit', 'credit'])
            ->setRowId('id');
    }

    public function query(Purchase $model): QueryBuilder
    {
        return $model->newQuery()->where('id', -1);
    }

    public function getLedgerData($supplier_id, $branch_id, $start_date, $end_date)
    {
        if (! $supplier_id) {
            return collect([]);
        }

        try {
            $prev_bill = Purchase::where('supplier_id', $supplier_id)
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id))
                ->where('date', '<', $start_date)
                ->sum('grand_total');

            $prev_paid = Purchase::where('supplier_id', $supplier_id)
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id))
                ->where('date', '<', $start_date)
                ->sum('paid_amount');

            $prev_return = PurchaseReturn::where('supplier_id', $supplier_id)
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id))
                ->where('status', 'approved')
                ->where('return_date', '<', $start_date)
                ->sum('grand_total');

            $opening_balance = $prev_bill - ($prev_paid + $prev_return);

            $purchases = Purchase::where('supplier_id', $supplier_id)
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id))
                ->whereBetween('date', [$start_date, $end_date])
                ->get()
                ->map(function ($row) {
                    return [
                        'date' => date('d-M-Y', strtotime($row->date)),
                        'description' => 'Purchase Invoice',
                        'reference_no' => $row->invoice_no,
                        'debit' => $row->paid_amount,
                        'credit' => $row->grand_total,
                        'timestamp' => strtotime($row->date.' '.$row->created_at),
                    ];
                });

            $returns = PurchaseReturn::where('supplier_id', $supplier_id)
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id))
                ->where('status', 'approved')
                ->whereBetween('return_date', [$start_date, $end_date])
                ->get()
                ->map(function ($row) {
                    return [
                        'date' => date('d-M-Y', strtotime($row->return_date)),
                        'description' => '<span class="text-danger">Purchase Return</span>',
                        'reference_no' => $row->reference_no ?? $row->return_no ?? '-',
                        'debit' => $row->grand_total,
                        'credit' => 0,
                        'timestamp' => strtotime($row->return_date.' '.$row->created_at),
                    ];
                });

            $ledger_data = $purchases->merge($returns)->sortBy('timestamp');

            $formatted_data = collect();
            $running_balance = $opening_balance;
            $period_total_credit = 0;
            $period_total_debit = 0;

            $formatted_data->push([
                'id' => 0,
                'date' => date('d-M-Y', strtotime($start_date)),
                'description' => '<strong>Opening Balance</strong>',
                'reference_no' => '-',
                'debit' => '-',
                'credit' => '-',
                'balance' => '<span class="fw-bold">'.\App\Helpers\CurrencyHelper::format($running_balance).'</span>',
            ]);

            foreach ($ledger_data as $index => $row) {
                $running_balance = $running_balance + $row['credit'] - $row['debit'];

                $period_total_credit += $row['credit'];
                $period_total_debit += $row['debit'];

                $formatted_data->push([
                    'id' => $index + 1,
                    'date' => $row['date'],
                    'description' => $row['description'],
                    'reference_no' => $row['reference_no'],
                    'debit' => $row['debit'] > 0 ? '<span class="text-danger">'.\App\Helpers\CurrencyHelper::format($row['debit']).'</span>' : '-',
                    'credit' => $row['credit'] > 0 ? '<span class="text-success">'.\App\Helpers\CurrencyHelper::format($row['credit']).'</span>' : '-',
                    'balance' => '<span class="fw-bold">'.\App\Helpers\CurrencyHelper::format($running_balance).'</span>',
                ]);
            }

            $formatted_data->push([
                'id' => 999999,
                'date' => '',
                'description' => '<div class="text-end fw-bold">Total Summary :</div>',
                'reference_no' => '',
                'credit' => '<span class="text-success fw-bold">'.\App\Helpers\CurrencyHelper::format($period_total_credit).'</span>',
                'debit' => '<span class="text-danger fw-bold">'.\App\Helpers\CurrencyHelper::format($period_total_debit).'</span>',
                'balance' => '<span class="fw-bold">'.\App\Helpers\CurrencyHelper::format($running_balance).'</span>',
            ]);

            return $formatted_data;

        } catch (\Exception $e) {
            return collect([
                [
                    'id' => 0,
                    'date' => '-',
                    'description' => '<span class="text-danger">Error: '.$e->getMessage().'</span>',
                    'reference_no' => '-',
                    'debit' => 0,
                    'credit' => 0,
                    'balance' => 0,
                ],
            ]);
        }
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('supplier-ledger-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white', 'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info btn-sm text-white', 'text' => '<i class="fas fa-print"></i> '.__('Print')],
                ],
                'searching' => false,
                'paging' => true,
                'ordering' => false,
                'headerCallback' => 'function(thead, data, start, end, display) {
                    $(thead).find("th").css("font-weight", "normal");
                }',
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('date')->title(__('Date')),
            Column::make('description')->title(__('Description')),
            Column::make('reference_no')->title(__('Reference No')),
            Column::make('credit')->title(__('Credit (Bill)'))->addClass('text-success'),
            Column::make('debit')->title(__('Debit (Paid/Return)'))->addClass('text-danger'),
            Column::make('balance')->title(__('Balance (Due)'))->addClass('fw-bold text-end'),
        ];
    }
}
