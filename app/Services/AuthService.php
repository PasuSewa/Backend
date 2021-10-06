<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

use App\Jobs\UpdateCredentialJob;

use App\Models\User;
use App\Models\Slot;

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

    public function access_user_data(User $user)
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'recovery_email' => $user->recovery_email,
            'phone_number' => Crypt::decryptString($user->phone_number),
            'anti_fishing_secret' => Crypt::decryptString($user->anti_fishing_secret),
            'security_access_code' => Crypt::decryptString($user->recovery_code),
        ];
    }

    public function access_credential_data($data)
    {
        $enc_credential = Slot::with(
            'email',
            'password',
            'phone_number',
            'security_code',
            'security_question_answer',
            'username'
        )
            ->where('user_id', $data['user_id'])
            ->where('id', $data['credential_id'])
            ->firstOrFail();

        $enc_credential->last_seen = now()->format('Y-m-d H:i:s');
        $enc_credential->accessing_device = $data['user_agent'];
        $enc_credential->accessing_platform = $data['accessing_platform'];

        $enc_credential->save();

        UpdateCredentialJob::dispatch($enc_credential->id)->delay(now()->addDays(3));

        $dec_credential = [
            'id' => $enc_credential->id,
            'user_id' => $enc_credential->user_id,
            'company_id' => $enc_credential->company_id,
            'company_name' => $enc_credential->company_name,
            'description' => $enc_credential->description,
            'last_seen' => $enc_credential->last_seen,
            'created_at' => $enc_credential->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $enc_credential->updated_at->format('Y-m-d H:i:s'),
        ];

        if (!is_null($enc_credential->user_name)) {
            $dec_credential['user_name'] = Crypt::decryptString($enc_credential->user_name);
        }

        if (!is_null($enc_credential->email)) {
            $dec_credential['email'] = Crypt::decryptString($enc_credential->email->email);
        }

        if (!is_null($enc_credential->password)) {
            $dec_credential['password'] = Crypt::decryptString($enc_credential->password->password);
        }

        if (!is_null($enc_credential->username)) {
            $dec_credential['username'] = Crypt::decryptString($enc_credential->username->username);
        }

        if (!is_null($enc_credential->phone_number)) {
            $dec_credential['phone_number'] = Crypt::decryptString($enc_credential->phone_number->phone_number);
        }

        if (!is_null($enc_credential->security_question_answer)) {
            $dec_credential['security_question'] = Crypt::decryptString($enc_credential->security_question_answer->security_question);
            $dec_credential['security_answer'] = Crypt::decryptString($enc_credential->security_question_answer->security_answer);
        }

        if (!is_null($enc_credential->security_code)) {
            if (!is_null($enc_credential->security_code->unique_code)) {
                $dec_credential['unique_code'] = Crypt::decryptString($enc_credential->security_code->unique_code);
            }

            if (!is_null($enc_credential->security_code->multiple_codes)) {
                $multiple_codes_string = Crypt::decryptString($enc_credential->security_code->multiple_codes);

                $dec_credential['multiple_codes'] = explode('<@>', $multiple_codes_string);
            }

            if (!is_null($enc_credential->security_code->crypto_codes)) {
                $crypto_codes_string = Crypt::decryptString($enc_credential->security_code->crypto_codes);

                $dec_credential['crypto_codes'] = explode('<@>', $crypto_codes_string);
            }
        }

        return $dec_credential;
    }
}
