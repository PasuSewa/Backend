<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

use PragmaRX\Google2FA\Google2FA;

use Validator;

use App\Models\User;

use App\Notifications\EmailTwoFactorAuth;

class AuthController extends Controller
{
    public function send_code_by_email(Request $request)
    {
        $data = $request->only('email', 'isSecondary');

        $validation = Validator::make($data, [
            'email' => ['required', 'email', 'min:5', 'max:190'],
            'isSecondary' => ['required', 'boolean'],
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => __('auth.failed'),
                'errors' => $validation->errors(),
                'request' => $request->all(),
                'status' => 401
            ], 401);
        }

        try {
            $user = $data['isSecondary']
                ? User::where('recovery_email', $data['email'])->firstOrFail()
                : User::where('email', $data['email'])->firstOrFail();
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('api_messages.error.user_was_not_found_or_isnt_allowed'),
                'errors' => [
                    'exception' => $th
                ],
                'request' => $request->all(),
                'status' => 404
            ], 404);
        }

        $code = rand(100000, 999999);

        $user->two_factor_code_email = Crypt::encryptString($code);

        $user->save();

        try {
            $antiFishingSecret = Crypt::decryptString($user->anti_fishing_secret);

            $user->notify(new EmailTwoFactorAuth($code, $antiFishingSecret, $user->preferred_lang));
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('api_messages.error.generic'),
                'errors' => [
                    'exception' => $th
                ],
                'status' => 500,
                'request' => null,
            ], 500);
        }

        return response()->json([
            'message' => __('api_messages.success.auth.email_sent'),
            'status' => 200
        ], 200);
    }

    public function create_user(Request $request)
    {
        $data = $request->only(
            'name',
            'phoneNumber',
            'mainEmail',
            'secondaryEmail',
            'secretAntiFishing',
            'secretAntiFishing_confirmation',
            'invitationCode'
        );

        $validation = Validator::make($data, [
            'name' => ['string', 'min:5', 'max:100', 'required'],
            'phoneNumber' => ['string', 'min:6', 'max:20', 'required'],
            'mainEmail' => ['email', 'min:5', 'max:190', 'unique:users,email', 'unique:users,recovery_email', 'required',],
            'secondaryEmail' => ['email', 'min:5', 'max:190', 'unique:users,email', 'unique:users,recovery_email', 'required',],
            'secretAntiFishing' => ['string', 'min:5', 'max:190', 'required', 'confirmed'],
            'secretAntiFishing_confirmation' => ['string', 'min:5', 'max:190', 'required',],
            'invitationCode' => ['string', 'min:10', 'max:10', 'exists:users,invitation_code', 'nullable'],
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 401,
                'errors' => $validation->errors(),
                'request' => $request->all(),
                'message' => __('api_messages.error.validation'),
            ], 401);
        }

        $slots_available = isset($data['invitationCode']) ? $this->spend_invitation_code($data['invitationCode']) : 5;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['mainEmail'],
            'recovery_email' => $data['secondaryEmail'],
            'phone_number' => Crypt::encryptString($data['phoneNumber']),
            'anti_fishing_secret' => Crypt::encryptString($data['secretAntiFishing']),
            'slots_available' =>  $slots_available,
            'invitation_code' => strtoupper(Str::random(15)),
            'two_factor_code_email' => Crypt::encryptString(rand(100000, 999999)),
            'two_factor_code_recovery' => Crypt::encryptString(rand(100000, 999999)),
            'preferred_lang' => app()->getLocale(),
            'recovery_code' => strtoupper(random_bytes(10))
        ]);

        return response()->json([
            'status' => 200,
            'registered_main_email' => $user->email,
            'message' => __('api_messages.success.auth.user_created'),
        ], 200);
    }

    public function verify_emails(Request $request)
    {
        $rules = ['required', 'integer', 'min:100000', 'max:999999'];

        $data = $request->only(
            'mainEmailCode',
            'recoveryEmailCode',
            'mainEmail'
        );

        $validation = Validator::make($data, [
            'mainEmailCode' => $rules,
            'recoveryEmailCode' => $rules,
            'mainEmail' => ['required', 'email', 'min:4', 'max:190', 'exists:users,email'],
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 401,
                'errors' => $validation->errors(),
                'request' => $request->all(),
                'message' => __('api_messages.error.validation'),
            ], 401);
        }

        $user = User::where('email', $data['mainEmail'])->firstOrFail();

        try {
            $main_code = Crypt::decryptString($user->two_factor_code_email);
            $second_code = Crypt::decryptString($user->two_factor_code_recovery);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('api_messages.error.generic'),
                'errors' => [
                    'exception' => $th
                ],
                'status' => 500,
                'request' => null
            ], 500);
        }

        $main_is_correct = $main_code === $data['mainEmailCode'];
        $second_is_correct = $second_code === $data['recoveryEmailCode'];

        if (!$main_is_correct || !$second_is_correct) {
            return response()->json([
                'status' => 401,
                'errors' => [
                    'error_message' => __('api_messages.error.validation')
                ],
                'message' => __('api_messages.error.validation'),
            ], 401);
        }

        $user->two_factor_code_email = null;
        $user->two_factor_code_recovery = null;
        $user->two_factor_secret = $this->generate_2fa_secret();
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => __('api_messages.success.auth.email_verified'),
            'auth_token' => auth('api')->tokenById($user->id)
        ], 200);
    }

    public function verify_2fa(Request $request)
    {
    }

    public function login_by_g2fa(Request $request)
    {
        $data = $request->only('email', '2fa_code');

        try {
            $user = User::where('email', $data['email'])->firstOrFail();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function login_by_email_code(Request $request)
    {
    }

    public function login_by_security_code(Request $request)
    {
    }

    public function logout(Request $request)
    {
    }

    public function refresh_2fa_secret(Request $request)
    {
        $user = $request->user();

        $secret = $this->generate_2fa_secret(true);

        $user->two_factor_secret = Crypt::encryptString($secret);
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => __('api_messages.success.auth.refresh_2fa_secret'),
            'secret' => $secret,
            'local' => app()->getLocale()
        ], 200);
    }

    private function generate_2fa_secret($return_decrypted = false)
    {
        $google2fa = new Google2FA();
        $two_factor_secret = $google2fa->generateSecretKey();

        if ($return_decrypted) {
            return $two_factor_secret;
        } else {
            return Crypt::encryptString($two_factor_secret);
        }
    }

    private function spend_invitation_code($code)
    {
        $referred_user = User::where('invitation_code', $code)->firstOrFail();

        if (!$referred_user->hasRole('premium')) {
            $referred_user->slots_available += 5;

            $referred_user->save();
        }

        return 10;
    }
}
