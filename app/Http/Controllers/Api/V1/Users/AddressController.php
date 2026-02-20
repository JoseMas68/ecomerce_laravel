<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Domain\Users\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Users\CreateAddressRequest;
use App\Http\Requests\V1\Users\UpdateAddressRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
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
     * Get all addresses for the authenticated user.
     *
     * GET /api/v1/users/addresses
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
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
    public function store(CreateAddressRequest $request): JsonResponse
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
    public function show(int $id): JsonResponse
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
    public function update(UpdateAddressRequest $request, int $id): JsonResponse
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
    public function destroy(int $id): JsonResponse
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
    public function setDefault(Request $request, int $id): JsonResponse
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
}
