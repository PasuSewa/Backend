<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function crypto_order_received(Request $request)
    {
    }

    public function crypto_order_failed(Request $request)
    {
    }

    public function crypto_order_succeeded(Request $request)
    {
    }

    private function verify_coinbase_signature($signature, $body)
    {
        /**
         * Now, here I have a problem. I tried verifying coinbase's signature header with their own php package, but the problem is that
            I don't know how to get the raw body of the request, and so, I couldn't use their package to verify it.
         * 
         * For now, unilt I figure out how to verify the signature, I'll be leaving this function like this.
         */
        return true;
    }
}
