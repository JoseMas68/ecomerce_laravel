<?php

namespace App\Domain\Users\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PasswordChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public bool $sendNotification = true
    ) {}
}
