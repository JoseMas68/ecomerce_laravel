<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shipping;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function calculate(Request $request): JsonResponse { return response()->json(['message' => 'Calculate shipping - TODO'], 501); }
    public function methods(): JsonResponse { return response()->json(['message' => 'Shipping methods - TODO'], 501); }
    public function zones(): JsonResponse { return response()->json(['message' => 'Shipping zones - TODO'], 501); }
    public function validatePostalCode(Request $request): JsonResponse { return response()->json(['message' => 'Validate postal code - TODO'], 501); }
}
