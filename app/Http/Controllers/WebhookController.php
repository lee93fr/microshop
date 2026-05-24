<?php
// app/Http/Controllers/WebhookController.php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function stripe(Request $request, PaymentService $paymentService)
    {
        try {
            $paymentService->handleWebhook(
                $request->getContent(),
                $request->header('Stripe-Signature'),
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(['received' => true]);
    }
}
