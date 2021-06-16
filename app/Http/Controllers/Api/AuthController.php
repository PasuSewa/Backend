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

        if($validation->fails())
        {
            return response()->json([
                'message' => __('auth.failed'),
                'errors' => $validation->errors()
            ], 200);
        }

        try 
        {
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

        } catch (\Throwable $th) 
        {
            return response()->json([
                'message' => __('api_message.error.generic'),
                'error' => $th
            ], 500);
        }
    }
}
