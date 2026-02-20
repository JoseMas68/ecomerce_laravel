<?php

declare(strict_types=1);

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();

            $table->enum('status', array_column(OrderStatus::cases(), 'value'))
                ->default(OrderStatus::PENDING->value);

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->string('currency', 3)->default('EUR');
            $table->string('shipping_method')->nullable();
            $table->string('payment_method')->nullable();

            $table->enum('payment_status', array_column(PaymentStatus::cases(), 'value'))
                ->default(PaymentStatus::PENDING->value);

            $table->json('shipping_address');
            $table->json('billing_address');
            $table->text('notes')->nullable();

            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('order_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
