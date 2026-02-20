<?php

declare(strict_types=1);

namespace App\Domain\Orders\Events;

use App\Domain\Orders\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderProcessing
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order
    ) {
    }
}