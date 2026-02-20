<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\DTOs\UserData;
use App\Domain\Users\Events\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;

class RegisterUser
{
    public function execute(UserData $data): User
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password ? Hash::make($data->password) : null,
            'phone' => $data->phone,
        ]);

        Event::dispatch(new UserRegistered($user));

        return $user;
    }
}
