<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function create(Request $request): JsonResponse { return response()->json(['message' => 'Create payment - TODO'], 501); }
    public function confirm(Request $request, $id): JsonResponse { return response()->json(['message' => 'Confirm payment - TODO'], 501); }
    public function show($id): JsonResponse { return response()->json(['message' => "Show payment {$id} - TODO"], 501); }
    public function methods(): JsonResponse { return response()->json(['message' => 'Payment methods - TODO'], 501); }
}
