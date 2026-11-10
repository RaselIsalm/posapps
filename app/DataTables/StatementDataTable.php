<?php

namespace App\DataTables;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StatementDataTable extends DataTable
{
    protected $balance;

    public function with(array|string $key, mixed $value = null): static
    {
        if ($key === 'balance') {
            $this->balance = $value;
        }

        return parent::with($key, $value);
    }

    public function dataTable(QueryBuilder $query)
    {
        $data = $query->get();
        $initialBalance = $this->balance ?? 0;

        if (request()->filled('account_id')) {
            $openingRow = new \stdClass;
            $openingRow->id = 'opening_row';
            $openingRow->date = request('start_date');
            $openingRow->reason = 'OPENING BALANCE';
            $openingRow->note = null;
            $openingRow->type = 'opening';
            $openingRow->amount = 0;
            $data->prepend($openingRow);

            // Calculate real totals ignoring the 'type' filter for accurate closing balance
            $realTotalCredit = Transaction::where('account_id', request('account_id'))
                ->when(request()->filled('start_date'), fn($q) => $q->where('date', '>=', request('start_date')))
                ->when(request()->filled('end_date'), fn($q) => $q->where('date', '<=', request('end_date')))
                ->where('type', 'credit')
                ->sum('amount');
                
            $realTotalDebit = Transaction::where('account_id', request('account_id'))
                ->when(request()->filled('start_date'), fn($q) => $q->where('date', '>=', request('start_date')))
                ->when(request()->filled('end_date'), fn($q) => $q->where('date', '<=', request('end_date')))
                ->where('type', 'debit')
                ->sum('amount');
                
            $finalBalance = $initialBalance + $realTotalCredit - $realTotalDebit;

            $closingRow = new \stdClass;
            $closingRow->id = 'closing_row';
            $closingRow->date = request('end_date') ?? date('Y-m-d');
            $closingRow->reason = 'CLOSING BALANCE';
            $closingRow->note = null;
            $closingRow->type = 'closing';
            $closingRow->amount = 0;
            $closingRow->final_balance = $finalBalance;
            $data->push($closingRow);
        }

        $runningBalance = $initialBalance;

        return datatables()->of($data)
            ->addIndexColumn()
            ->editColumn('date', function ($row) {
                if ($row->type === 'closing') {
                    return '';
                }

                return date('d M, Y', strtotime($row->date));
            })
            ->addColumn('description', function ($row) {
                if ($row->type === 'opening') {
                    return '<span class="fw-bold text-primary">OPENING BALANCE (B/F)</span>';
                }
                if ($row->type === 'closing') {
                    return '<span class="fw-bold text-dark text-uppercase" style="font-size: 1.1em;">CLOSING BALANCE</span>';
                }

                $html = '<span class="fw-bold text-dark">'.$row->reason.'</span>';
                if ($row->note) {
                    $html .= '<br><small class="text-muted">'.$row->note.'</small>';
                }

                return $html;
            })
            ->addColumn('credit', function ($row) {
                if ($row->type === 'opening' || $row->type === 'closing') {
                    return '-';
                }

                return $row->type == 'credit'
                    ? '<span class="text-success fw-bold">'.\App\Helpers\CurrencyHelper::format($row->amount).'</span>'
                    : '-';
            })
            ->addColumn('debit', function ($row) {
                if ($row->type === 'opening' || $row->type === 'closing') {
                    return '-';
                }

                return $row->type == 'debit'
                    ? '<span class="text-danger fw-bold">'.\App\Helpers\CurrencyHelper::format($row->amount).'</span>'
                    : '-';
            })
            ->addColumn('balance', function ($row) use (&$runningBalance) {
                if ($row->type === 'closing') {
                    return '<span class="fw-bold text-dark" style="font-family:monospace; font-size: 1.2em;">'.\App\Helpers\CurrencyHelper::format($row->final_balance).'</span>';
                }
                if ($row->type === 'opening') {
                    return '<span class="fw-bold" style="font-family:monospace">'.\App\Helpers\CurrencyHelper::format($runningBalance).'</span>';
                }

                // If filtered by type, intermediate running balance is mathematically inaccurate
                if (request()->filled('type') && request('type') != 'all') {
                    return '<span class="text-muted">-</span>';
                }

                if ($row->type == 'credit') {
                    $runningBalance += $row->amount;
                } else {
                    $runningBalance -= $row->amount;
                }

                return '<span class="fw-bold" style="font-family:monospace">'.\App\Helpers\CurrencyHelper::format($runningBalance).'</span>';
            })
            ->rawColumns(['description', 'credit', 'debit', 'balance'])
            ->setRowId('id');
    }

    public function query(Transaction $model): QueryBuilder
    {
        $query = $model->newQuery();

        if (! request()->filled('account_id')) {
            return $query->whereRaw('1 = 0');
        }

        if (request()->filled('account_id')) {
            $query->where('account_id', request('account_id'));
        }

        if (request()->filled('type') && request('type') != 'all') {
            $query->where('type', request('type'));
        }

        if (request()->filled('start_date')) {
            $query->where('date', '>=', request('start_date'));
        }

        if (request()->filled('end_date')) {
            $query->where('date', '<=', request('end_date'));
        }

        return $query->orderBy('date', 'asc')->orderBy('id', 'asc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('statement-table')
            ->columns($this->getColumns())
            ->ajax(['url' => url()->full()])
            ->dom('Bfrtip')
            ->orderBy(1)
            ->selectStyleSingle()
            ->parameters([
                'paging' => false,
                'searching' => true,
                'info' => false,
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white', 'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info btn-sm text-white', 'text' => '<i class="fas fa-print"></i> '.__('Print')],
                ],
                'language' => ['search' => '', 'searchPlaceholder' => __('Search...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false),
            Column::make('date')->title(__('Date'))->orderable(false),
            Column::make('description')->title(__('Description'))->orderable(false),
            Column::make('credit')->title(__('Credit (+)'))->class('text-end')->orderable(false),
            Column::make('debit')->title(__('Debit (-)'))->class('text-end')->orderable(false),
            Column::make('balance')->title(__('Balance'))->class('text-end')->orderable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Account_Statement_'.date('YmdHis');
    }
}
