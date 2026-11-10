<?php

namespace App\Console\Commands;

use App\Models\ProductBranch;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Illuminate\Console\Command;

class CheckStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify inventory managers about products that have reached their low-stock threshold';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $lowStockItems = ProductBranch::with(['product', 'branch'])
            ->whereHas('product', function ($query) {
                $query->whereColumn('product_branches.qty', '<=', 'products.alert_quantity');
            })
            ->get();

        if ($lowStockItems->isEmpty()) {
            $this->info('No low-stock items found.');

            return self::SUCCESS;
        }

        $recipients = User::permission('view_inventory')->get();

        if ($recipients->isEmpty()) {
            $this->info('No users with inventory permission to notify.');

            return self::SUCCESS;
        }

        $sentCount = 0;

        foreach ($lowStockItems as $item) {
            if (! $item->product || ! $item->branch) {
                continue;
            }

            $message = sprintf(
                'Low stock alert: "%s" at %s has %s left (threshold: %s).',
                $item->product->product_title,
                $item->branch->name,
                $this->formatQty($item->qty),
                $this->formatQty($item->product->alert_quantity)
            );

            foreach ($recipients as $user) {
                $alreadyNotified = $user->notifications()
                    ->where('type', StockAlertNotification::class)
                    ->whereNull('read_at')
                    ->where('data->product_branch_id', $item->id)
                    ->exists();

                if ($alreadyNotified) {
                    continue;
                }

                $user->notify(new StockAlertNotification($message, route('inventory.index'), $item->id));
                $sentCount++;
            }
        }

        $this->info("Checked {$lowStockItems->count()} low-stock item(s), sent {$sentCount} notification(s).");

        return self::SUCCESS;
    }

    private function formatQty($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    }
}
