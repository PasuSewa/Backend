<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Validator;

class PaymentsController extends Controller
{
    private $coinbase_shared_secret;
    private $paypal_base_uri;
    private $paypal_client_id;
    private $paypal_client_secret;

    public function __construct()
    {
        $this->coinbase_shared_secret = env('COINBASE_SHARED_SECRET');
        $this->paypal_base_uri = env('PAYPAL_BASE_URI');
        $this->paypal_client_id = env('PAYPAL_CLIENT_ID');
        $this->paypal_client_secret = env('PAYPAL_CLIENT_SECRET');
    }

    /**************************************************************************************************************** init payment instance */
    public function start_payment_instance(Request $request)
    {
        $data = $request->only('method', 'amount', 'type', 'code');

        $validation = Validator::make($data, [
            'method' => ['required', 'string', 'min:6', 'max:6', 'in:PayPal,Crypto'],
            'amount' => ['required', 'integer', 'min:5'],
            'type' => ['required', 'string', 'min:5', 'max:7', 'in:premium,slots']
        ]);

        if ($validation->fails()) {
            $data = [
                'errors' => $validation->errors(),
                'request' => $request->all(),
            ];

            return response()->error($data, 'api_messages.error.parameter_was_incorrect', 400);
        }

        $user = $request->user();

        PaymentInstance::create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'code' => $data['code'],
            'type' => $data['type'],
            'method' => $data['method'],
        ]);

        return response()->success([], 'payment_instance_started');
    }

    /**************************************************************************************************************** coinbase webhooks */
    public function crypto_order_received(Request $request)
    {
        $signature_verified = $this->verify_signature($request);

        if (!$signature_verified) {
            return response()->error([
                'errors' => [
                    'message' => __('api_messages.error.coinbase_signature_failed')
                ],
                'request' => $request->all(),
            ], 'api_messages.error.coinbase_signature_failed', 400);
        }

        $data = $request->all();

        $order_code = $data['event']['data']['code'];

        return response()->success([], 'coinbase_webhook_received');
    }

    public function crypto_order_failed(Request $request)
    {
        $signature_verified = $this->verify_signature($request);

        if (!$signature_verified) {
            return response()->error([
                'errors' => [
                    'message' => __('api_messages.error.coinbase_signature_failed')
                ],
                'request' => $request->all(),
            ], 'api_messages.error.coinbase_signature_failed', 400);
        }

        $data = $request->all();

        $order_code = $data['event']['data']['code'];

        return response()->success([], 'coinbase_webhook_received');
    }

    public function crypto_order_succeeded(Request $request)
    {
        $signature_verified = $this->verify_signature($request);

        if (!$signature_verified) {
            return response()->error([
                'errors' => [
                    'message' => __('api_messages.error.coinbase_signature_failed')
                ],
                'request' => $request->all(),
            ], 'api_messages.error.coinbase_signature_failed', 400);
        }

        $data = $request->all();

        $order_code = $data['event']['data']['code'];

        return response()->success([], 'coinbase_webhook_received');
    }

    private function verify_signature(Request $request)
    {
        /**
         * Now, here I have a problem. I tried verifying coinbase's signature header with their own php package, but the problem is that
            I don't know how to get the raw body of the request, and so, I couldn't use their package to verify it.
         * 
         * For now, unilt I figure out how to verify the signature, I'll be leaving this function like this.
         */

        $signature = $request->header('X-CC-Webhook-Signature');

        $body = $request->getContent();

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

    private function capture_paypal_order($paypal_order_id)
    {
        $credentials = base64_encode("{$this->paypal_client_id}:{$this->paypal_client_secret}");

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Basic {$credentials}"
        ])
            ->post(
                $this->paypal_base_uri . "/v2/checkout/orders/{$paypal_order_id}/capture",
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
