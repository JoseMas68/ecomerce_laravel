<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Domain\Users\Models\Address;
use App\Domain\Users\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Users\ChangePasswordRequest;
use App\Http\Requests\V1\Users\CreateAddressRequest;
use App\Http\Requests\V1\Users\UpdateAddressRequest;
use App\Http\Requests\V1\Users\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected UserService $userService;

    /**
     * Create a new controller instance.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get the authenticated user's profile.
     *
     * GET /api/v1/users/profile
     *
     * @return JsonResponse
     */
    public function profile(): JsonResponse
    {
        try {
            $user = auth()->user();
            $user->load(['addresses', 'defaultShippingAddress', 'defaultBillingAddress']);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                ],
                'message' => 'Profile retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the authenticated user's profile.
     *
     * PUT/PATCH /api/v1/users/profile
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $updatedUser = $this->userService->updateProfile($user, $request->validated());

            $updatedUser->load(['addresses', 'defaultShippingAddress', 'defaultBillingAddress']);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $updatedUser,
                ],
                'message' => 'Profile updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error updating profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change the authenticated user's password.
     *
     * POST /api/v1/users/change-password
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Current password is incorrect',
                ], 401);
            }

            $this->userService->changePassword($user, $request->new_password);

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Password changed successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error changing password: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload avatar for the authenticated user.
     *
     * POST /api/v1/users/upload-avatar
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => ['required', 'image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
            ]);

            $user = auth()->user();

            $avatarUrl = $this->userService->uploadAvatar($user, $request->file('file'));

            return response()->json([
                'success' => true,
                'data' => [
                    'avatar_url' => $avatarUrl,
                ],
                'message' => 'Avatar uploaded successfully',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error uploading avatar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete the authenticated user's account.
     *
     * DELETE /api/v1/users/account
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'password' => ['required', 'string'],
            ]);

            $user = auth()->user();

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Password is incorrect',
                ], 401);
            }

            $this->userService->deleteAccount($user);

            auth()->guard('web')->logout();
            auth()->guard('sanctum')->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Account deleted successfully',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error deleting account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all addresses for the authenticated user.
     *
     * GET /api/v1/users/addresses
     *
     * @return JsonResponse
     */
    public function addresses(): JsonResponse
    {
        try {
            $user = auth()->user();
            $addresses = $this->userService->getUserAddresses($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'addresses' => $addresses,
                ],
                'message' => 'Addresses retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving addresses: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new address for the authenticated user.
     *
     * POST /api/v1/users/addresses
     *
     * @param CreateAddressRequest $request
     * @return JsonResponse
     */
    public function storeAddress(CreateAddressRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $address = $this->userService->createAddress($user, $request->validated());

            return response()->json([
                'success' => true,
                'data' => [
                    'address' => $address,
                ],
                'message' => 'Address created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error creating address: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific address for the authenticated user.
     *
     * GET /api/v1/users/addresses/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function showAddress(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            $address = $user->addresses()->where('id', $id)->first();

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Address not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'address' => $address,
                ],
                'message' => 'Address retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving address: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an address for the authenticated user.
     *
     * PUT/PATCH /api/v1/users/addresses/{id}
     *
     * @param UpdateAddressRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateAddress(UpdateAddressRequest $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            $address = $user->addresses()->where('id', $id)->first();

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Address not found',
                ], 404);
            }

            $updatedAddress = $this->userService->updateAddress($address, $request->validated());

            return response()->json([
                'success' => true,
                'data' => [
                    'address' => $updatedAddress,
                ],
                'message' => 'Address updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error updating address: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an address for the authenticated user.
     *
     * DELETE /api/v1/users/addresses/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteAddress(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            $address = $user->addresses()->where('id', $id)->first();

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Address not found',
                ], 404);
            }

            $this->userService->deleteAddress($address);

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Address deleted successfully',
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error deleting address: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set an address as default (shipping or billing).
     *
     * POST /api/v1/users/addresses/{id}/set-default
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function setDefaultAddress(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'type' => ['required', 'string', 'in:shipping,billing'],
            ]);

            $user = auth()->user();

            $address = $user->addresses()->where('id', $id)->first();

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Address not found',
                ], 404);
            }

            $updatedAddress = $this->userService->setDefaultAddress($user, $address, $request->type);

            return response()->json([
                'success' => true,
                'data' => [
                    'address' => $updatedAddress,
                ],
                'message' => 'Default address updated successfully',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error setting default address: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics for the authenticated user.
     *
     * GET /api/v1/users/stats
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $user = auth()->user();
            $stats = $this->userService->getUserStats($user);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'User statistics retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving statistics: ' . $e->getMessage(),
            ], 500);
        }
    }
}
