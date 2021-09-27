<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\PaymentInstance;
use App\Models\User;

use App\Notifications\PaymentFailed;
use App\Notifications\PaymentPending;
use App\Notifications\PaymentSucceeded;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

use Validator;

class PaymentsController extends Controller
{
    private $coinbase_shared_secret;
    private $paypal_base_uri;
    private $paypal_client_id;
    private $paypal_client_secret;
    private $use_fake_payments;

    public function __construct()
    {
        $this->coinbase_shared_secret = env('COINBASE_SHARED_SECRET');
        $this->paypal_base_uri = env('PAYPAL_BASE_URI');
        $this->paypal_client_id = env('PAYPAL_CLIENT_ID');
        $this->paypal_client_secret = env('PAYPAL_CLIENT_SECRET');
        $this->use_fake_payments = env('USE_FAKE_PAYMENTS');
    }

    /**************************************************************************************************************** init payment instance */
    public function start_payment_instance(Request $request)
    {
        $data = $request->only('method', 'amount', 'type', 'code');

        $validation = Validator::make($data, [
            'method' => ['required', 'string', 'min:6', 'max:6', 'in:PayPal,Crypto'],
            'amount' => ['required', 'integer', 'min:5'],
            'type' => ['required', 'string', 'min:5', 'max:7', 'in:premium,slots'],
            'code' => ['required', 'string', 'min:1', 'max:190', 'unique:payment_instances,code']
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

        $secret = Crypt::decryptString($user->anti_fishing_secret);

        $user->notify(new PaymentPending($secret, $user->preferred_lang));

        return response()->success([], 'payment_instance_started');
    }

    /**************************************************************************************************************** get payment instance */
    private function get_payment_instance($code)
    {
        try {
            if (!$this->use_fake_payments) {
                return [
                    'instance' => PaymentInstance::where('code', $code)->firstOrFail(),
                    'successful' => true,
                ];
            } else {
                return [
                    'successful' => true,
                    'instance' => null,
                ];
            }
        } catch (\Throwable $th) {
            return [
                'successful' => false,
                'message' => $th->getMessage(),
                'errors' => $th
            ];
        }
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

        $payment = $this->get_payment_instance($data['event']['data']['code']);

        if (!$payment['successful']) {
            return response()->error(['errors' => $payment['errors']], $payment['message'], 500);
        }

        $user = User::find(!$this->use_fake_payments ? $payment['instance']->user_id : 1);

        $secret = Crypt::decryptString($user->anti_fishing_secret);

        $user->notify(new PaymentPending($secret, $user->preferred_lang));

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

        $payment = $this->get_payment_instance($data['event']['data']['code']);

        if (!$payment['successful']) {
            return response()->error(['errors' => $payment['errors']], $payment['message'], 500);
        }

        $user = User::find(!$this->use_fake_payments ? $payment['instance']->user_id : 1);

        $secret = Crypt::decryptString($user->anti_fishing_secret);

        $user->notify(new PaymentFailed($secret, $user->preferred_lang));

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

        $payment = $this->get_payment_instance($order_code);

        if (!$payment['successful']) {
            return response()->error(['errors' => $payment['errors']], $payment['message'], 500);
        }

        $user = User::find(!$this->use_fake_payments ? $payment['instance']->user_id : 1);

        $secret = Crypt::decryptString($user->anti_fishing_secret);

        $resolve_purchase = $this->resolve_purchase($order_code);

        if (!$resolve_purchase) {

            $user->notify(new PaymentFailed($secret, $user->preferred_lang));

            return response()->error([
                'errors' => __('api_messages.error.generic')
            ], 'api_messages.error.generic', 500);
        }

        $user->notify(new PaymentSucceeded($secret, $user->preferred_lang));

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
        $data = $request->only('code');

        $validation = Validator::make($data, [
            'code' => ['required', 'string', 'min:1', 'max:190', 'exists:payment_instances,code']
        ]);

        if ($validation->fails()) {
            $data = [
                'errors' => $validation->errors(),
                'request' => $request->all(),
            ];

            return response()->error($data, 'api_messages.error.parameter_was_incorrect', 400);
        }

        $payment = $this->capture_paypal_order($data['code']);

        $user = $request->user();

        $secret = Crypt::decryptString($user->anti_fishing_secret);

        if (!$this->use_fake_payments && $payments['status'] !== 'COMPLETED') {

            $user->notify(new PaymentFailed($secret, $user->preferred_lang));

            return response()->error(
                [
                    'errors' => [
                        'message' => 'message',
                        'paypal_response' => $payment
                    ],
                    'request' => $request->all(),
                ],
                'api_messages.error.error_paying_with_paypal',
                500,
            );
        }

        $resolve_purchase = $this->resolve_purchase($data['code']);

        if (!$resolve_purchase) {

            $user->notify(new PaymentFailed($secret, $user->preferred_lang));

            return response()->error([
                'errors' => __('api_messages.error.generic')
            ], 'api_messages.error.generic', 500);
        }

        $user->notify(new PaymentSucceeded($secret, $user->preferred_lang));

        return response()->success([], 'purchase_finished');
    }

    private function capture_paypal_order($paypal_order_id)
    {
        if ($this->use_fake_payments) {
            return [
                'status' => 'COMPLETED'
            ];
        }

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

    /**************************************************************************************************************** give the user what they paid for */
    private function resolve_purchase($code)
    {
        $payment = $this->get_payment_instance($code);

        if (!$payment['successful']) {
            return false;
        }

        $user = User::find(!$this->use_fake_payments ? $payment['instance']->user_id : 1);

        if ($this->use_fake_payments) {
            return true;
        }

        if ($payment['instance']->type === 'premium') {

            if ($user->hasRole('free')) {
                $user->removeRole('free');
            }

            if ($user->hasRole('semi-premium')) {
                $user->removeRole('semi-premium');
            }

            $user->assignRole('premium');

            $payment['instance']->delete();

            return true;
        }

        if ($payment['instance']->type === 'slots') {
            $slots_to_add = $payment['instance']->amount / 10;

            if (is_int($slots_to_add)) {
                $user->available_slots *= $slots_to_add;

                $payment['instance']->delete();

                return true;
            } // end if is_int

            return false;
        } // end if payment type

        return false;
    } // end method
}
