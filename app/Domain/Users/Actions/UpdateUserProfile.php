<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\DTOs\UpdateProfileData;
use App\Domain\Users\Events\UserUpdated;
use App\Models\User;
use Illuminate\Support\Facades\Event;

class UpdateUserProfile
{
    public function execute(User $user, UpdateProfileData $data): User
    {
        $changedFields = [];

        if ($data->name !== null && $user->name !== $data->name) {
            $user->name = $data->name;
            $changedFields[] = 'name';
        }

        if ($data->email !== null && $user->email !== $data->email) {
            $user->email = $data->email;
            $changedFields[] = 'email';
        }

        if ($data->phone !== null && $user->phone !== $data->phone) {
            $user->phone = $data->phone;
            $changedFields[] = 'phone';
        }

        if (!empty($changedFields)) {
            $user->save();
            Event::dispatch(new UserUpdated($user, $changedFields));
        }

        return $user->fresh();
    }
}
