<?php

declare(strict_types=1);

namespace App\Domain\Users\Services;

use App\Domain\Users\Models\Address;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Update user profile.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    /**
     * Change user password.
     *
     * @param User $user
     * @param string $newPassword
     * @return void
     */
    public function changePassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Upload user avatar.
     *
     * @param User $user
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    public function uploadAvatar(User $user, mixed $file): string
    {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $file->store('avatars/' . $user->id, 'public');

        $user->update([
            'avatar' => $path,
        ]);

        return Storage::disk('public')->url($path);
    }

    /**
     * Delete user account.
     *
     * @param User $user
     * @return void
     */
    public function deleteAccount(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->addresses()->delete();
            $user->tokens()->delete();

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();
        });
    }

    /**
     * Get all addresses for a user.
     *
     * @param User $user
     * @return Collection<int, Address>
     */
    public function getUserAddresses(User $user): Collection
    {
        return $user->addresses()->orderBy('is_default_shipping', 'desc')
            ->orderBy('is_default_billing', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new address for a user.
     *
     * @param User $user
     * @param array $data
     * @return Address
     */
    public function createAddress(User $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['is_default_shipping']) && $data['is_default_shipping']) {
                $user->addresses()->update(['is_default_shipping' => false]);
            }

            if (isset($data['is_default_billing']) && $data['is_default_billing']) {
                $user->addresses()->update(['is_default_billing' => false]);
            }

            return $user->addresses()->create($data);
        });
    }

    /**
     * Update an existing address.
     *
     * @param Address $address
     * @param array $data
     * @return Address
     */
    public function updateAddress(Address $address, array $data): Address
    {
        return DB::transaction(function () use ($address, $data) {
            $user = $address->user;

            if (isset($data['is_default_shipping']) && $data['is_default_shipping']) {
                $user->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default_shipping' => false]);
            }

            if (isset($data['is_default_billing']) && $data['is_default_billing']) {
                $user->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default_billing' => false]);
            }

            $address->update($data);

            return $address->fresh();
        });
    }

    /**
     * Delete an address.
     *
     * @param Address $address
     * @return void
     */
    public function deleteAddress(Address $address): void
    {
        $address->delete();
    }

    /**
     * Set an address as default (shipping or billing).
     *
     * @param User $user
     * @param Address $address
     * @param string $type
     * @return Address
     */
    public function setDefaultAddress(User $user, Address $address, string $type): Address
    {
        return DB::transaction(function () use ($user, $address, $type) {
            $field = $type === 'shipping' ? 'is_default_shipping' : 'is_default_billing';

            $user->addresses()
                ->where('id', '!=', $address->id)
                ->update([$field => false]);

            $address->update([$field => true]);

            return $address->fresh();
        });
    }

    /**
     * Get user statistics.
     *
     * @param User $user
     * @return array<string, mixed>
     */
    public function getUserStats(User $user): array
    {
        $totalOrders = $user->orders()->count();
        $completedOrders = $user->orders()
            ->where('status', 'completed')
            ->count();
        $pendingOrders = $user->orders()
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        $totalSpent = (float) $user->orders()
            ->where('status', 'completed')
            ->sum('total');

        $averageOrderValue = $completedOrders > 0
            ? $totalSpent / $completedOrders
            : 0;

        $addressCount = $user->addresses()->count();

        $lastOrder = $user->orders()
            ->orderBy('created_at', 'desc')
            ->first();

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'pending_orders' => $pendingOrders,
            'total_spent' => number_format($totalSpent, 2, '.', ''),
            'average_order_value' => number_format($averageOrderValue, 2, '.', ''),
            'address_count' => $addressCount,
            'last_order_date' => $lastOrder?->created_at?->toIso8601String(),
            'member_since' => $user->created_at->toIso8601String(),
        ];
    }
}
