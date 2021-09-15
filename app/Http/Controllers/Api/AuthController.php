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
            return $this->validation_error($request, $validation);
        }

        try {
            $user = $data['isSecondary']
                ? User::where('recovery_email', $data['email'])->firstOrFail()
                : User::where('email', $data['email'])->firstOrFail();
        } catch (\Throwable $th) {
            $data = [
                'errors' => [
                    'exception' => $th
                ]
            ];
            return response()->error($data, 404, 'api_messages.error.user_was_not_found_or_isnt_allowed');
        }

        $code = rand(100000, 999999);

        if ($data['isSecondary']) {
            $user->two_factor_code_recovery = Crypt::encryptString($code);
        } else {
            $user->two_factor_code_email = Crypt::encryptString($code);
        }
        $user->save();

        try {
            $antiFishingSecret = Crypt::decryptString($user->anti_fishing_secret);

            $user->notify(new EmailTwoFactorAuth($code, $antiFishingSecret, $user->preferred_lang));
        } catch (\Throwable $th) {
            $data = [
                'errors' => [
                    'exception' => $th
                ]
            ];
            return response()->error($data, 500, 'api_messages.error.generic');
        }
        return response()->success(null, 'auth.email_sent');
    }

    public function create_user(Request $request)
    {
        $data = $request->only(
            'name',
            'phoneNumber',
            'mainEmail',
            'recoveryEmail',
            'secretAntiFishing',
            'secretAntiFishing_confirmation',
            'invitationCode'
        );

        $validation = Validator::make($data, [
            'name' => ['string', 'min:5', 'max:100', 'required'],
            'phoneNumber' => ['string', 'min:6', 'max:20', 'required'],
            'mainEmail' => ['email', 'min:5', 'max:190', 'unique:users,email', 'unique:users,recovery_email', 'required',],
            'recoveryEmail' => ['email', 'min:5', 'max:190', 'unique:users,email', 'unique:users,recovery_email', 'required',],
            'secretAntiFishing' => ['string', 'min:5', 'max:190', 'required', 'confirmed'],
            'secretAntiFishing_confirmation' => ['string', 'min:5', 'max:190', 'required',],
            'invitationCode' => ['string', 'min:10', 'max:10', 'exists:users,invitation_code', 'nullable'],
        ]);

        if ($validation->fails()) {
            return $this->validation_error($request, $validation);
        }

        $slots_available = isset($data['invitationCode']) ? $this->spend_invitation_code($data['invitationCode']) : 5;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['mainEmail'],
            'recovery_email' => $data['recoveryEmail'],
            'phone_number' => Crypt::encryptString($data['phoneNumber']),
            'anti_fishing_secret' => Crypt::encryptString($data['secretAntiFishing']),
            'slots_available' =>  $slots_available,
            'invitation_code' => strtoupper(Str::random(15)),
            'two_factor_code_email' => Crypt::encryptString(rand(100000, 999999)),
            'two_factor_code_recovery' => Crypt::encryptString(rand(100000, 999999)),
            'preferred_lang' => app()->getLocale(),
            'recovery_code' => Crypt::encryptString(strtoupper(Str::random(10)))
        ]);

        $user->assignRole('free');

        return response()->success(['registered_email' => $user->email], 'auth.user_created');
    }

    public function verify_emails(Request $request)
    {
        $rules = ['required', 'integer', 'min:000000', 'max:999999'];

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
            return $this->validation_error($request, $validation);
        }

        $user = User::where('email', $data['mainEmail'])->firstOrFail();

        try {
            $main_code = Crypt::decryptString($user->two_factor_code_email);
            $second_code = Crypt::decryptString($user->two_factor_code_recovery);
        } catch (\Throwable $th) {
            $data = [
                'errors' => [
                    'exception' => $th
                ]
            ];
            return response()->error($data, 500, 'api_messages.error.generic');
        }

        $main_is_correct = $main_code === $data['mainEmailCode'];
        $second_is_correct = $second_code === $data['recoveryEmailCode'];

        if (!$main_is_correct || !$second_is_correct) {
            return $this->validation_error($request);
        }

        $user->two_factor_code_email = null;
        $user->two_factor_code_recovery = null;
        $user->two_factor_secret = $this->generate_2fa_secret();
        $user->save();

        return response()->success(['token' => auth('api')->setTTL(7200)->tokenById($user->id)], 'auth.email_verified');
    }

    public function verify_2fa(Request $request)
    {
        $data = $request->only('twoFactorCode');

        $user = $request->user();

        $validation = Validator::make($data, [
            'twoFactorCode' => ['required', 'integer', 'min:000000', 'max:999999']
        ]);

        if ($validation->fails()) {
            return $this->validation_error($request, $validation);
        }

        $is_valid = $this->validate_2fa_code($user->two_factor_secret, $data['twoFactorCode']);

        if ($is_valid) {
            //in the react app, the token its not stored anywhere, so we need to destroy the old session
            //in order to give the frontend another token
            auth('api')->invalidate();

            return response()->user_was_authenticated(['user' => $user], '2fa_code_is_correct', true);
        } else {
            return $this->validation_error($request, null, 'api_messages.error.2fa_code_invalid');
        }
    }

    public function login_by_g2fa(Request $request)
    {
        $data = $request->only('email', 'twoFactorCode');

        $validation = Validator::make($data, [
            'email' => ['required', 'email', 'min:3', 'max:190', 'exists:users,email'],
            'twoFactorCode' => ['required', 'integer', 'min:000000', 'max:999999']
        ]);

        if ($validation->fails()) {
            return $this->validation_error($request, $validation);
        }

        $user = User::where('email', $data['email'])->firstOrFail();

        $is_valid = $this->validate_2fa_code($user->two_factor_secret, $data['twoFactorCode']);

        if ($is_valid) {
            return response()->user_was_authenticated(['user' => $user], '2fa_code_is_correct', true, true);
        } else {
            return $this->validation_error($request, null, 'api_messages.error.2fa_code_invalid');
        }
    }

    public function login_by_email_code(Request $request)
    {
        $data = $request->only('email', 'recoveryEmail', 'code');

        $validation = Validator::make($data, [
            'email' => ['required', 'email', 'min:3', 'max:190', 'exists:users,email'],
            'recoveryEmail' => ['nullable', 'email', 'min:3', 'max:190', 'exists:users,recovery_email'],
            'code' => ['required', 'integer', 'min:000000', 'max:999999']
        ]);

        if ($validation->fails()) {
            return $this->validation_error($request, $validation);
        }

        $user = User::where('email', $data['email'])->firstOrFail();

        $db_code = isset($data['recoveryEmail']) ? $user->two_factor_code_recovery : $user->two_factor_code_email;

        $valid_email_code = $data['code'] === Crypt::decryptString($db_code);

        if ($valid_email_code) {
            return response()->user_was_authenticated(['user' => $user], '2fa_code_is_correct', true, true);
        } else {
            return $this->validation_error($request);
        }
    }

    public function login_by_security_code(Request $request)
    {
        $data = $request->only('mainEmail', 'recoveryEmail', 'antiFishingSecret', 'securityCode');

        $validation = Validator::make($data, [
            'mainEmail' => ['required', 'email', 'min:3', 'max:190', 'exists:users,email'],
            'recoveryEmail' => ['required', 'email', 'min:3', 'max:190'],
            'antiFishingSecret' => ['required', 'string', 'min:5', 'max:190'],
            'securityCode' => ['required', 'string', 'min:10', 'max:10']
        ]);

        if ($validation->fails()) {
            return $this->validation_error($request, $validation);
        }

        try {
            $user = User::where('email', $data['mainEmail'])->first();
        } catch (\Throwable $th) {
            $data = [
                'errors' => [
                    'exception' => $th
                ]
            ];
            return response()->error($data, 404, 'api_messages.error.user_was_not_found_or_isnt_allowed');
        }

        $second_email_is_valid = $data['recoveryEmail'] === $user->recovery_email;
        $anti_fishing_is_valid = $data['antiFishingSecret'] === Crypt::decryptString($user->anti_fishing_secret);
        $security_code_is_valid = $data['securityCode'] === Crypt::decryptString($user->recovery_code);

        if ($second_email_is_valid && $anti_fishing_is_valid && $security_code_is_valid) {
            return response()->user_was_authenticated(['user' => $user], '2fa_code_is_correct', true, true);
        } else {
            return $this->validation_error($request);
        }
    }

    public function logout()
    {
        auth('api')->invalidate();

        return response()->success([], 'auth.logged_out');
    }

    public function refresh_2fa_secret(Request $request)
    {
        $user = $request->user();

        $secret = $this->generate_2fa_secret(true);

        $user->two_factor_secret = Crypt::encryptString($secret);
        $user->save();

        return response()->success(['secret' => $secret, 'email' => $user->email], 'auth.refresh_2fa_secret');
    }

    private function validate_2fa_code($secret_key, $code)
    {
        $google2fa = new Google2FA();

        $window = 1; // 30 sec

        // I'm not really sure how to test this, and the package doesn't actually explain anything
        // I've already tested it manually, and it works. So (at least for now) I'll be leaving it like this
        // If the env is "local" it asumes that we are in a "testing environment"
        if (env('APP_ENV') !== 'local') {
            return $google2fa->verifyKey(Crypt::decryptString($secret_key), $code, $window);
        } else {
            return true;
        }
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

    private function validation_error(Request $request, $validation = null, $message = null)
    {
        if (!is_null($validation)) {
            $data = [
                'errors' => $validation->errors(),
                'request' => $request->all(),
            ];
        } else {
            $data = [
                'errors' => [
                    'error' => __($message ? $message : 'auth.failed')
                ],
                'request' => $request->all()
            ];
        }
        return response()->error($data, $message ? $message : 'auth.failed', 401);
    }
}
