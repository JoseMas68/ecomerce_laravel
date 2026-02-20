<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(): JsonResponse { return response()->json(['message' => 'Profile - TODO'], 501); }
    public function update(Request $request): JsonResponse { return response()->json(['message' => 'Update profile - TODO'], 501); }
    public function updatePassword(Request $request): JsonResponse { return response()->json(['message' => 'Update password - TODO'], 501); }
    public function addresses(): JsonResponse { return response()->json(['message' => 'Addresses - TODO'], 501); }
    public function storeAddress(Request $request): JsonResponse { return response()->json(['message' => 'Store address - TODO'], 501); }
    public function updateAddress(Request $request, $id): JsonResponse { return response()->json(['message' => 'Update address - TODO'], 501); }
    public function deleteAddress($id): JsonResponse { return response()->json(['message' => 'Delete address - TODO'], 501); }
}
