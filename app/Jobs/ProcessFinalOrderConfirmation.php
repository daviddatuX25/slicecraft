<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessFinalOrderConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Load relationships safely
        $this->order->loadMissing(['customer', 'items']);

        $customerName = $this->order->customer?->name ?? 'Valued Customer';
        $customerEmail = $this->order->customer?->email ?? 'no-email';
        $itemCount = $this->order->items->count();
        $totalPesos = number_format($this->order->total_amount_cents / 100, 2);

        // Record asynchronous confirmation log
        Log::info(sprintf(
            '[FINAL-EXAM-QUEUE] Asynchronous order confirmation processed for Order #%s. Recipient: %s <%s>. Items: %d. Total: PHP %s.',
            $this->order->order_number,
            $customerName,
            $customerEmail,
            $itemCount,
            $totalPesos
        ));
    }
}
