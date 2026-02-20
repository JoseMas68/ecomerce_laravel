<?php

namespace App\Domain\Users\Events;

use App\Domain\Users\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddressCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Address $address,
        public User $user
    ) {}
}
