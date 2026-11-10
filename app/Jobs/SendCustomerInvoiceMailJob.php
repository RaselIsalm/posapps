<?php

namespace App\Jobs;

use App\Mail\DueInvoiceMail;
use App\Mail\PaidInvoiceMail;
use App\Mail\PartialInvoiceMail;
use App\Models\Customer;
use App\Models\Sale;
use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCustomerInvoiceMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $sale;

    /**
     * Create a new job instance.
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->sale->loadMissing(['customer', 'branch', 'productSales.product', 'payments']);

            $customer = $this->sale->customer;

            // Check if customer exists and has a valid email
            if (! $customer || empty($customer->email)) {
                return;
            }

            $to = $customer->email;

            // Decide which mailable to send based on payment status
            if ($this->sale->payment_status === 'paid') {
                $mailable = new PaidInvoiceMail($this->sale);
            } elseif ($this->sale->payment_status === 'partial') {
                $mailable = new PartialInvoiceMail($this->sale);
            } else {
                $mailable = new DueInvoiceMail($this->sale);
            }

            // Send using MailService which automatically configures the SMTP settings
            MailService::send($to, $mailable);

            Log::info("Invoice mail sent successfully to {$to} for Sale ID: {$this->sale->id}");

        } catch (\Exception $e) {
            Log::error('Failed to send invoice mail in job', [
                'sale_id' => $this->sale->id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw if you want the queue to retry, but for emails it's often better to just log
            // throw $e;
        }
    }
}
