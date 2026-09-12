<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController as RootPaymentController;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function create(Request $request)
    {
        return app(RootPaymentController::class)->processPayment($request);
    }

    public function processPayment(Request $request)
    {
        return app(RootPaymentController::class)->processPayment($request);
    }
}
