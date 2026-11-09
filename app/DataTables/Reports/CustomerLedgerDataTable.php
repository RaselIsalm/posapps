<?php

namespace App\DataTables\Reports;

use App\Helpers\InvoiceHelper;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CustomerLedgerDataTable extends DataTable
{
    public function dataTable($query)
    {
        $request = $this->request();
        $customer_id = $request->get('customer_id');
        $branch_id = $request->get('branch_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        $data = $this->getLedgerData($customer_id, $branch_id, $start_date, $end_date);

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

    public function query(Sale $model): QueryBuilder
    {
        return $model->newQuery()->where('id', -1);
    }

    public function getLedgerData($customer_id, $branch_id, $start_date, $end_date)
    {
        if (! $customer_id) {
            return collect([]);
        }

        try {
            $is_walk_in = ($customer_id === 'walk_in');

            // Sales IDs for this customer and branch
            $customer_sale_ids = Sale::query()
                ->when($is_walk_in, fn ($q) => $q->whereNull('customer_id'))
                ->when(! $is_walk_in, fn ($q) => $q->where('customer_id', $customer_id))
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id))
                ->whereIn('delivery_status', ['delivered', 'completed', 'pending'])
                ->pluck('id');

            // Previous Balance Calculation
            $prev_sale = Sale::query()
                ->when($is_walk_in, fn ($q) => $q->whereNull('customer_id'))
                ->when(! $is_walk_in, fn ($q) => $q->where('customer_id', $customer_id))
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id))
                ->whereIn('delivery_status', ['delivered', 'completed', 'pending'])
                ->where('sale_date', '<', $start_date)
                ->sum('grand_total');

            $prev_payment = Payment::whereIn('sale_id', $customer_sale_ids)
                ->where('payment_date', '<', $start_date)
                ->sum('amount');

            $prev_return = SaleReturn::query()
                ->when($is_walk_in, fn ($q) => $q->whereNull('customer_id'))
                ->when(! $is_walk_in, fn ($q) => $q->where('customer_id', $customer_id))
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id))
                ->where('status', 'approved')
                ->where('date', '<', $start_date)
                ->sum('grand_total');

            $opening_balance = $prev_sale - ($prev_payment + $prev_return);

            // Current Period Data
            $sales = Sale::query()
                ->when($is_walk_in, fn ($q) => $q->whereNull('customer_id'))
                ->when(! $is_walk_in, fn ($q) => $q->where('customer_id', $customer_id))
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id))
                ->whereIn('delivery_status', ['delivered', 'completed', 'pending'])
                ->whereBetween('sale_date', [$start_date, $end_date])
                ->get()
                ->map(function ($row) {
                    return [
                        'date' => date('d-M-Y', strtotime($row->sale_date)),
                        'description' => 'Sale Invoice',
                        'reference_no' => $row->invoice_no,
                        'debit' => $row->grand_total,
                        'credit' => 0,
                        'timestamp' => strtotime($row->sale_date.' '.$row->created_at),
                    ];
                });

            $payments = Payment::whereIn('sale_id', $customer_sale_ids)
                ->whereBetween('payment_date', [$start_date, $end_date])
                ->get()
                ->map(function ($row) {
                    return [
                        'date' => date('d-M-Y', strtotime($row->payment_date)),
                        'description' => 'Payment Received ('.(InvoiceHelper::resolvePaymentMethodName($row->payment_method) ?: 'Cash').')',
                        'reference_no' => $row->transaction_id ?? '-',
                        'debit' => 0,
                        'credit' => $row->amount,
                        'timestamp' => strtotime($row->payment_date.' '.$row->created_at),
                    ];
                });

            $returns = SaleReturn::query()
                ->when($is_walk_in, fn ($q) => $q->whereNull('customer_id'))
                ->when(! $is_walk_in, fn ($q) => $q->where('customer_id', $customer_id))
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id))
                ->where('status', 'approved')
                ->whereBetween('date', [$start_date, $end_date])
                ->get()
                ->map(function ($row) {
                    return [
                        'date' => date('d-M-Y', strtotime($row->date)),
                        'description' => '<span class="text-danger">Sale Return</span>',
                        'reference_no' => $row->reference_no ?? '-',
                        'debit' => 0,
                        'credit' => $row->grand_total,
                        'timestamp' => strtotime($row->date.' '.$row->created_at),
                    ];
                });

            $ledger_data = $sales->merge($payments)->merge($returns)->sortBy('timestamp');

            $formatted_data = collect();
            $running_balance = $opening_balance;
            $period_total_debit = 0;
            $period_total_credit = 0;

            $formatted_data->push([
                'id' => 0,
                'date' => date('d-M-Y', strtotime($start_date)),
                'description' => '<strong>Opening Balance</strong>',
                'reference_no' => '-',
                'debit' => '-',
                'credit' => '-',
                'balance' => '<span class="">'.\App\Helpers\CurrencyHelper::format($running_balance).'</span>',
            ]);

            foreach ($ledger_data as $index => $row) {
                $running_balance = $running_balance + $row['debit'] - $row['credit'];
                $period_total_debit += $row['debit'];
                $period_total_credit += $row['credit'];

                $formatted_data->push([
                    'id' => $index + 1,
                    'date' => $row['date'],
                    'description' => $row['description'],
                    'reference_no' => $row['reference_no'],
                    'debit' => $row['debit'] > 0 ? '<span class="text-danger">'.\App\Helpers\CurrencyHelper::format($row['debit']).'</span>' : '-',
                    'credit' => $row['credit'] > 0 ? '<span class="text-success">'.\App\Helpers\CurrencyHelper::format($row['credit']).'</span>' : '-',
                    'balance' => '<span class="">'.\App\Helpers\CurrencyHelper::format($running_balance).'</span>',
                ]);
            }

            $formatted_data->push([
                'id' => 999999,
                'date' => '',
                'description' => '<div class="text-end fw-bold">Total Summary :</div>',
                'reference_no' => '',
                'debit' => '<span class="text-danger fw-bold">'.\App\Helpers\CurrencyHelper::format($period_total_debit).'</span>',
                'credit' => '<span class="text-success fw-bold">'.\App\Helpers\CurrencyHelper::format($period_total_credit).'</span>',
                'balance' => '<span class="fw-bold">'.\App\Helpers\CurrencyHelper::format($running_balance).'</span>',
            ]);

            return $formatted_data;
        } catch (\Exception $e) {
            return collect([
                [
                    'id' => 0,
                    'date' => '-',
                    'description' => 'Error: '.$e->getMessage(),
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
            ->setTableId('customer-ledger-table')
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
            Column::make('debit')->title(__('Debit (Bill)'))->addClass('text-danger'),
            Column::make('credit')->title(__('Credit (Paid/Return)'))->addClass('text-success'),
            Column::make('balance')->title(__('Balance (Due)'))->addClass('text-end'),
        ];
    }
}
