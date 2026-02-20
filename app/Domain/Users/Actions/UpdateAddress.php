<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\DTOs\AddressData;
use App\Domain\Users\Events\AddressUpdated;
use App\Domain\Users\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class UpdateAddress
{
    public function execute(Address $address, AddressData $data): Address
    {
        return DB::transaction(function () use ($address, $data) {
            $changedFields = [];
            $originalData = $address->toArray();

            $addressData = $data->toArray();
            unset($addressData['user_id']);

            foreach ($addressData as $key => $value) {
                $snakeKey = str_replace('_', '', $key);
                $camelKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));

                if (isset($originalData[$key]) && $originalData[$key] != $value) {
                    $address->$key = $value;
                    $changedFields[] = $key;
                }
            }

            if ($data->isDefaultShipping) {
                $address->user->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default_shipping' => false]);
            }

            if ($data->isDefaultBilling) {
                $address->user->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default_billing' => false]);
            }

            if (!empty($changedFields) || $data->isDefaultShipping || $data->isDefaultBilling) {
                $address->save();
                Event::dispatch(new AddressUpdated($address, $address->user, $changedFields));
            }

            return $address->fresh();
        });
    }
}
