<?php

namespace App\Services;

use App\Models\User;
use App\Models\PaymentInstance;

use Illuminate\Support\Facades\Http;

class PaymentService
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

    /**************************************************************************************************************** give the user what they paid for */
    public function resolve_purchase($code)
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

            $user->slots_available = 3;
            $user->save();

            return true;
        }

        if ($payment['instance']->type === 'slots') {
            $slots_to_add = $payment['instance']->amount / 10;

            if (is_int($slots_to_add)) {
                $user->slots_available *= $slots_to_add;

                $payment['instance']->delete();

                return true;
            } // end if is_int

            return false;
        } // end if payment type

        return false;
    } // end of method

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

    /**************************************************************************************************************** paypal */
    public function capture_paypal_order($paypal_order_id)
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

    /**************************************************************************************************************** coinbase */
    public function verify_signature(Request $request)
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
}
