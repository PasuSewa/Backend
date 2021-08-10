<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Validator;

use App\Models\User;

use Illuminate\Support\Facades\Crypt;

use PragmaRX\Google2FA\Google2FA;

use App\Notifications\EmailTwoFactorAuth;

class AuthController extends Controller
{
    public function sendCodeByEmail(Request $request)
    {
        $data = $request->only('email', 'isSecondary');

        $validation = Validator::make($data, [
            'email' => ['required', 'email'],
            'isSecondary' => ['required', 'boolean'],
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => __('auth.failed'),
                'errors' => $validation->errors()
            ], 200);
        }

        try {
            $user = $data['isSecondary']
                ? User::where('recovery_email', $data['email'])->firstOrFail()
                : User::where('email', $data['email'])->firstOrFail();

            $code = rand(100000, 999999);

            $user->two_factor_code_email = Crypt::encryptString($code);

            $user->save();

            $antiFishingSecret = Crypt::decryptString($user->anti_fishing_secret);

            $user->notify(new EmailTwoFactorAuth($code, $antiFishingSecret, $user->preferred_lang));

            return response()->json([
                'message' => __('api_messages.success.auth.email_sent')
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('api_message.error.generic'),
                'error' => $th
            ], 500);
        }
    }

    public function createUser(Request $request)
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

        $validation = Validation::make($data, [
            'name' => ['string', 'min:5', 'max:100', 'required'],
            'phoneNumber' => ['string', 'min:6', 'max:20', 'required'],
            'mainEmail' => ['email', 'min:5', 'max:190', 'unique:users,email', 'unique:users,recovery_email', 'required'],
            'secondaryEmail' => ['email', 'min:5', 'max:190', 'unique:users,email', 'unique:users,recovery_email', 'required'],
            'secretAntiFishing' => ['string', 'min:5', 'max:190', 'required'],
            'secretAntiFishing_confirmation' => ['string', 'min:5', 'max:190', 'required'],
            'invitationCode' => ['string', 'min:10', 'max:10', 'exists:users,invitation_code', 'nullable']
        ]);

        return response()->json([
            'message' => __('auth.failed'),
            'errors' => $validation->errors()
        ], 200);
    }

    public function loginByG2FA(Request $request)
    {
    }

    public function loginByEmailCode(Request $request)
    {
    }

    public function loginBySecurityCode(Request $request)
    {
    }

    public function logout(Request $request)
    {
    }

    public function generateG2FASecretKey()
    {
        // ya que no se puede mostrar la secret key bajo ninguna circunstancia, voy a tener que hacer otro componente
        // que genere una nueva secret key...
    }
}
