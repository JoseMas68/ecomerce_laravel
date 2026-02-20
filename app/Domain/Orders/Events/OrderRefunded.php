<?php

declare(strict_types=1);

namespace App\Domain\Orders\Events;

use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderRefunded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public Payment $payment,
        public float $refundAmount
    ) {
    }
}
