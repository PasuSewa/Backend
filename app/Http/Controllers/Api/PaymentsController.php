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

use Validator;

use App\Services\PaymentService;

class PaymentsController extends Controller
{
    private $use_fake_payments;

    public function __construct()
    {
        $this->use_fake_payments = env('USE_FAKE_PAYMENTS');
    }

    /**************************************************************************************************************** init payment instance */
    /**
     * Start Payment Instance
     * 
     * This method will create an "open payment instance" in the database, so, when the payment is fully confirmed, 
     * the backend will know how much the user has paid, and what did they actually purchased.
     * 
     * @group Payments
     * 
     * @authenticated
     * 
     * @header Accept-Language es | en | jp
     * 
     * @bodyParam method string required One of two options, either "PayPal" or "Crypto" (be careful, don't forget the capital letters)
     * @bodyParam amount integer required The amount (in USD) that the user is paying
     * @bodyParam type string required Either one of two options, "premium" if purchasing premium role, or "slots" if paying for more slots
     * @bodyParam code string required The id of the transaction in PayPal, or the code of the transaction in Coinbase
     * 
     * @response status=200 scenario="success" {
     *      "status": 200,
     *      "message": "Succes!",
     *      "data": {},
     * }
     * 
     * @response status=400 scenario="validation failed" {
     *      "status": 400,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "body": "body must have at least 5 characters."
     *              }
     *          ],
     *          "request": {
     *              "method": "PayPal",
     *              "amount": 100,
     *              "code": "AAAAA"
     *              "type": "", 
     *          }
     *      }
     * }
     */
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

    /**************************************************************************************************************** coinbase webhooks */
    public function crypto_order_received(Request $request)
    {
        $signature_verified = (new PaymentService())->verify_signature($request);

        if (!$signature_verified) {
            return response()->error([
                'errors' => [
                    'message' => __('api_messages.error.coinbase_signature_failed')
                ],
                'request' => $request->all(),
            ], 'api_messages.error.coinbase_signature_failed', 400);
        }

        $data = $request->all();

        $payment = (new PaymentService())->get_payment_instance($data['event']['data']['code']);

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
        $signature_verified = (new PaymentService())->verify_signature($request);

        if (!$signature_verified) {
            return response()->error([
                'errors' => [
                    'message' => __('api_messages.error.coinbase_signature_failed')
                ],
                'request' => $request->all(),
            ], 'api_messages.error.coinbase_signature_failed', 400);
        }

        $data = $request->all();

        $payment = (new PaymentService())->get_payment_instance($data['event']['data']['code']);

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
        $signature_verified = (new PaymentService())->verify_signature($request);

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

        $payment = (new PaymentService())->get_payment_instance($order_code);

        if (!$payment['successful']) {
            return response()->error(['errors' => $payment['errors']], $payment['message'], 500);
        }

        $user = User::find(!$this->use_fake_payments ? $payment['instance']->user_id : 1);

        $secret = Crypt::decryptString($user->anti_fishing_secret);

        $resolve_purchase = (new PaymentService())->resolve_purchase($order_code);

        if (!$resolve_purchase) {

            $user->notify(new PaymentFailed($secret, $user->preferred_lang));

            return response()->error([
                'errors' => __('api_messages.error.generic')
            ], 'api_messages.error.generic', 500);
        }

        $user->notify(new PaymentSucceeded($secret, $user->preferred_lang));

        return response()->success([], 'coinbase_webhook_received');
    }

    /**************************************************************************************************************** paypal */
    /**
     * Verify PayPal Payment
     * 
     * This method recieves the transaction id of PayPal, and verifies that the purchase is fully confirmed
     * 
     * @group Payments
     * 
     * @authenticated
     * 
     * @header Accept-Language es | en | jp
     * 
     * @bodyParam code string required The id of the transaction in PayPal
     * 
     * @response {
     *      "status": 200,
     *      "message": "Succes!",
     *      "data": {}
     * }
     * 
     * @response status=404 scenario="validation failed" {
     *      "status": 404,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "code": "the code doesn't exist in database"
     *              }
     *          ],
     *          "request": {
     * "code": "AAAAA"
     *          }
     *      }
     * }
     */
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

            return response()->error($data, 'api_messages.error.parameter_was_incorrect', 404);
        }

        $payment = (new PaymentService())->capture_paypal_order($data['code']);

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

        $resolve_purchase = (new PaymentService())->resolve_purchase($data['code']);

        if (!$resolve_purchase) {

            $user->notify(new PaymentFailed($secret, $user->preferred_lang));

            return response()->error([
                'errors' => __('api_messages.error.generic')
            ], 'api_messages.error.generic', 500);
        }

        $user->notify(new PaymentSucceeded($secret, $user->preferred_lang));

        return response()->success([], 'purchase_finished');
    }
}
