<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\Events\PasswordChanged;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

class ChangePassword
{
    public function execute(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        Event::dispatch(new PasswordChanged($user));

        return true;
    }
}
