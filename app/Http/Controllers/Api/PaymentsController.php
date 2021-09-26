<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Validator;

class PaymentsController extends Controller
{
    protected $shared_secret;

    public function __construct()
    {
        $this->shared_secret = env('COINBASE_SHARED_SECRET');
    }

    /**************************************************************************************************************** init payment instance */
    public function start_payment_instance(Request $request)
    {
        /**
         * validate request
         * request must contain:
         * 
         * 1- jwt token
         * 2- method = 'PayPal' || 'Crypto'
         * 3- amount
         * 4- type = 'role' || 'slots'
         * 5- code = paypal payment id || coinbase order code
         */

        /**
         * to do:
         * rename model OpenPayment => PaymentInstance
         * create paymnet instance on db
         * return response
         */
    }

    /**************************************************************************************************************** coinbase webhooks */
    public function crypto_order_received(Request $request)
    {
        $signature = $request->header('X-CC-Webhook-Signature');

        $body = $request->getContent();

        $signature_verified = $this->verify_signature($signature, $body);

        if ($signature_verified) {

            $data = $request->all();

            $order_code = $data['event']['data']['code'];

            return response()->success([], 'coinbase_webhook_received');
        } else {
            return response()->error([
                'errors' => __('api_messages.error.coinbase_signature_failed'),
                'request' => $request->all(),
            ], 'api_messages.error.coinbase_signature_failed', 400);
        }
    }

    public function crypto_order_failed(Request $request)
    {
        $signature = $request->header('X-CC-Webhook-Signature');

        $body = $request->getContent();

        $signature_verified = $this->verify_signature($signature, $body);

        if ($signature_verified) {

            $data = $request->all();

            $order_code = $data['event']['data']['code'];

            return response()->success([], 'coinbase_webhook_received');
        } else {
            return response()->error([
                'errors' => __('api_messages.error.coinbase_signature_failed'),
                'request' => $request->all(),
            ], 'api_messages.error.coinbase_signature_failed', 400);
        }
    }

    public function crypto_order_succeeded(Request $request)
    {
        $signature = $request->header('X-CC-Webhook-Signature');

        $body = $request->getContent();

        $signature_verified = $this->verify_signature($signature, $body);

        if ($signature_verified) {

            $data = $request->all();

            $order_code = $data['event']['data']['code'];

            return response()->success([], 'coinbase_webhook_received');
        } else {
            return response()->error([
                'errors' => __('api_messages.error.coinbase_signature_failed'),
                'request' => $request->all(),
            ], 'api_messages.error.coinbase_signature_failed', 400);
        }
    }

    private function verify_signature($signature, $body)
    {
        /**
         * Now, here I have a problem. I tried verifying coinbase's signature header with their own php package, but the problem is that
            I don't know how to get the raw body of the request, and so, I couldn't use their package to verify it.
         * 
         * For now, unilt I figure out how to verify the signature, I'll be leaving this function like this.
         */
        return true;
    }

    /**************************************************************************************************************** paypal */
    public function verify_paypal_payment(Request $request)
    {
        // request from the frontend

        // $payment = $this->capturePaypalPayment($data['paypalOrderId']);

        // if ($payment['status'] !== 'COMPLETED') {
        //     return response()->error(
        //         [
        //             'errors' => [
        //                 'message' => 'message',
        //                 'paypal_response' => $payment
        //             ],
        //             'request' => $request->all(),
        //         ],
        //         'api_messages.error.error_paying_with_paypal',
        //         500,
        //     );
        // }

        // resolve request
    }

    public function capture_paypal_order($paypal_order_id)
    {
        $base_uri = env('PAYPAL_BASE_URI');
        $client_id = env('PAYPAL_CLIENT_ID');
        $client_secret = env('PAYPAL_CLIENT_SECRET');

        $credentials = base64_encode("{$client_id}:{$client_secret}");

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Basic {$credentials}"
        ])
            ->post(
                $base_uri . "/v2/checkout/orders/{$paypal_order_id}/capture",
                [
                    'application_context' =>
                    [
                        'return_url' => 'https://pasunashi.xyz/paypal/payment-succeeded',
                        'cancel_url' => 'https://pasunashi.xyz/paypal/payment-failed'
                    ]
                ]
            )
            ->json();
    }
}
