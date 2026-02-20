<?php

declare(strict_types=1);

namespace App\Domain\Orders\Actions;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentStatus;
use App\Domain\Orders\Events\OrderRefunded;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessRefund
{
    public function execute(
        Order $order,
        float $refundAmount,
        string $reason = 'Refund requested'
    ): Order {
        try {
            DB::beginTransaction();

            if (!$order->canBeRefunded()) {
                throw new \Exception('Order cannot be refunded. Current status: ' . $order->status->value);
            }

            if ($refundAmount > $order->total) {
                throw new \Exception('Refund amount cannot exceed order total');
            }

            $payment = $order->payments()
                ->where('status', PaymentStatus::COMPLETED)
                ->latest()
                ->first();

            if (!$payment) {
                throw new \Exception('No completed payment found for this order');
            }

            $this->processRefund($payment, $refundAmount, $reason);

            if ($refundAmount >= $order->total) {
                $order->status = OrderStatus::REFUNDED;
            }

            $order->save();

            DB::commit();

            event(new OrderRefunded($order, $payment, $refundAmount));

            Log::info('Order refund processed', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'refund_amount' => $refundAmount,
                'reason' => $reason,
            ]);

            return $order->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to process refund', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function processRefund(Payment $payment, float $refundAmount, string $reason): void
    {
        $gateway = $payment->payment_gateway ?? 'stripe';

        match ($gateway) {
            'stripe' => $this->processStripeRefund($payment, $refundAmount, $reason),
            'paypal' => $this->processPayPalRefund($payment, $refundAmount, $reason),
            'cod' => $this->processCashOnDeliveryRefund($payment, $refundAmount, $reason),
            default => throw new \Exception("Refund not supported for gateway: {$gateway}"),
        };
    }

    private function processStripeRefund(Payment $payment, float $refundAmount, string $reason): void
    {
        if ($refundAmount >= $payment->amount) {
            $payment->markAsRefunded();
        } else {
            $payment->markAsPartiallyRefunded();
        }

        $metadata = $payment->metadata ?? [];
        $metadata['refund'] = [
            'amount' => $refundAmount,
            'reason' => $reason,
            'refunded_at' => now()->toIso8601String(),
        ];
        $payment->metadata = $metadata;
        $payment->save();
    }

    private function processPayPalRefund(Payment $payment, float $refundAmount, string $reason): void
    {
        if ($refundAmount >= $payment->amount) {
            $payment->markAsRefunded();
        } else {
            $payment->markAsPartiallyRefunded();
        }

        $metadata = $payment->metadata ?? [];
        $metadata['refund'] = [
            'amount' => $refundAmount,
            'reason' => $reason,
            'refunded_at' => now()->toIso8601String(),
        ];
        $payment->metadata = $metadata;
        $payment->save();
    }

    private function processCashOnDeliveryRefund(Payment $payment, float $refundAmount, string $reason): void
    {
        if ($refundAmount >= $payment->amount) {
            $payment->markAsRefunded();
        } else {
            $payment->markAsPartiallyRefunded();
        }

        $metadata = $payment->metadata ?? [];
        $metadata['refund'] = [
            'amount' => $refundAmount,
            'reason' => $reason,
            'refunded_at' => now()->toIso8601String(),
        ];
        $payment->metadata = $metadata;
        $payment->save();
    }
}
