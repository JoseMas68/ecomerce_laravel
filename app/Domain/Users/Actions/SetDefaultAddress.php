<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\Events\AddressUpdated;
use App\Domain\Users\Models\Address;
use Illuminate\Support\Facades\Event;

class SetDefaultAddress
{
    public function execute(Address $address, string $type): Address
    {
        $isDefaultShipping = $type === 'shipping' || $type === 'both';
        $isDefaultBilling = $type === 'billing' || $type === 'both';

        if ($isDefaultShipping) {
            $address->setAsDefaultShipping();
        }

        if ($isDefaultBilling) {
            $address->setAsDefaultBilling();
        }

        Event::dispatch(new AddressUpdated(
            $address->fresh(),
            $address->user,
            [$isDefaultShipping ? 'is_default_shipping' : null, $isDefaultBilling ? 'is_default_billing' : null]
        ));

        return $address->fresh();
    }
}
