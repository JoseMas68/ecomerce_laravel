<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\Events\AddressDeleted;
use App\Domain\Users\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\Event;

class DeleteAddress
{
    public function execute(Address $address): bool
    {
        $user = $address->user;
        $addressId = $address->id;

        $result = $address->delete();

        if ($result) {
            Event::dispatch(new AddressDeleted($addressId, $user));
        }

        return $result;
    }
}
