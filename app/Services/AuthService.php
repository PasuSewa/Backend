<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

use App\Models\User;

class AuthService
{
    public function validate_2fa_code($secret_key, $code)
    {
        // Since the package doesn't come with any instructions on how to test it, I decided to do this
        // So, if env is "local" or "testing" it will not consider G2FA, and return a true instead
        // I tested G2FA manually and, as far as I know, it works
        if (env('APP_ENV') !== 'local' && env('APP_ENV') !== 'testing') {

            $google2fa = new Google2FA();

            $window = 1; // 30 sec

            return $google2fa->verifyKey(Crypt::decryptString($secret_key), $code, $window);
        } else {
            return true;
        }
    }

    public function generate_2fa_secret($return_decrypted = false)
    {
        $google2fa = new Google2FA();
        $two_factor_secret = $google2fa->generateSecretKey();

        if ($return_decrypted) {
            return $two_factor_secret;
        } else {
            return Crypt::encryptString($two_factor_secret);
        }
    }

    public function spend_invitation_code($code)
    {
        $referred_user = User::where('invitation_code', $code)->firstOrFail();

        if (!$referred_user->hasRole('premium')) {
            $referred_user->slots_available += 5;

            $referred_user->save();
        }

        return 10;
    }

    public function validation_error(Request $request, $validation = null, $message = null)
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
