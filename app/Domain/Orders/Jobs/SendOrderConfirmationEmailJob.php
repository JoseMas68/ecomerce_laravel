<?php

declare(strict_types=1);

namespace App\Domain\Orders\Jobs;

use App\Domain\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public Order $order
    ) {
    }

    public function handle(): void
    {
        try {
            $user = $this->order->user;

            if (!$user || !$user->email) {
                Log::warning('Cannot send order confirmation: no user or email', [
                    'order_id' => $this->order->id,
                ]);

                return;
            }

            Mail::to($user->email)->send(new \App\Mail\OrderConfirmationMail($this->order));

            Log::info('Order confirmation email sent', [
                'order_id' => $this->order->id,
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
