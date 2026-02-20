<?php

namespace App\Domain\Users\Events;

use App\Domain\Users\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddressDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $addressId,
        public User $user
    ) {}
}
