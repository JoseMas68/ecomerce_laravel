<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function stripe(Request $request): \Illuminate\Http\Response
    {
        return response()->json(['message' => 'Stripe webhook - TODO'], 501);
    }

    public function paypal(Request $request): \Illuminate\Http\Response
    {
        return response()->json(['message' => 'PayPal webhook - TODO'], 501);
    }
}
