<?php

namespace App\Domain\Users\Policies;

use App\Domain\Users\Models\Address;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    public function delete(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }
}
