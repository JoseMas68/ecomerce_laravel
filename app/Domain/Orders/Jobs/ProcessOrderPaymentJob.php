<?php

declare(strict_types=1);

namespace App\Domain\Orders\Jobs;

use App\Domain\Orders\Enums\PaymentStatus;
use App\Domain\Orders\Events\OrderPaid;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessOrderPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public Order $order,
        public string $paymentGateway,
        public array $paymentData
    ) {
    }

    public function handle(): void
    {
        try {
            DB::beginTransaction();

            $payment = $this->order->payments()->create([
                'payment_gateway' => $this->paymentGateway,
                'amount' => $this->order->total,
                'currency' => $this->order->currency,
                'status' => PaymentStatus::PENDING,
                'metadata' => $this->paymentData,
            ]);

            $result = $this->processPayment($payment);

            if ($result['success']) {
                $payment->markAsPaid($result['transaction_id'] ?? null);
                $this->order->payment_status = PaymentStatus::COMPLETED;
                $this->order->status = \App\Domain\Orders\Enums\OrderStatus::PROCESSING;
                $this->order->save();

                event(new OrderPaid($this->order, $payment));

                Log::info('Payment processed successfully', [
                    'order_id' => $this->order->id,
                    'payment_id' => $payment->id,
                ]);
            } else {
                $payment->markAsFailed($result['message'] ?? 'Unknown error');

                Log::error('Payment processing failed', [
                    'order_id' => $this->order->id,
                    'payment_id' => $payment->id,
                    'error' => $result['message'] ?? 'Unknown error',
                ]);

                throw new \Exception('Payment processing failed: ' . ($result['message'] ?? 'Unknown error'));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Payment job failed', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function processPayment(Payment $payment): array
    {
        return match ($this->paymentGateway) {
            'stripe' => $this->processStripePayment($payment),
            'paypal' => $this->processPayPalPayment($payment),
            'cod' => $this->processCashOnDelivery($payment),
            default => ['success' => false, 'message' => 'Invalid payment gateway'],
        };
    }

    private function processStripePayment(Payment $payment): array
    {
        return [
            'success' => true,
            'transaction_id' => 'stripe_' . uniqid(),
        ];
    }

    private function processPayPalPayment(Payment $payment): array
    {
        return [
            'success' => true,
            'transaction_id' => 'paypal_' . uniqid(),
        ];
    }

    private function processCashOnDelivery(Payment $payment): array
    {
        return [
            'success' => true,
            'transaction_id' => 'cod_' . uniqid(),
        ];
    }
}
