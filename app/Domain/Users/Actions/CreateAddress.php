<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\DTOs\AddressData;
use App\Domain\Users\Events\AddressCreated;
use App\Domain\Users\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class CreateAddress
{
    public function execute(User $user, AddressData $data): Address
    {
        return DB::transaction(function () use ($user, $data) {
            if ($data->isDefaultShipping) {
                $user->addresses()->update(['is_default_shipping' => false]);
            }

            if ($data->isDefaultBilling) {
                $user->addresses()->update(['is_default_billing' => false]);
            }

            $address = $user->addresses()->create($data->toArray());

            Event::dispatch(new AddressCreated($address, $user));

            return $address;
        });
    }
}
