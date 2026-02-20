<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->after('name', function (Blueprint $table) {
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
                $table->unsignedBigInteger('default_shipping_address_id')->nullable();
                $table->unsignedBigInteger('default_billing_address_id')->nullable();
            });

            $table->foreign('default_shipping_address_id')->references('id')->on('addresses')->nullOnDelete();
            $table->foreign('default_billing_address_id')->references('id')->on('addresses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['default_shipping_address_id']);
            $table->dropForeign(['default_billing_address_id']);
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'avatar',
                'default_shipping_address_id',
                'default_billing_address_id',
            ]);
        });
    }
};
