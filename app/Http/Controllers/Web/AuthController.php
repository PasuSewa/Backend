<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\EmailTwoFactorAuth;
use App\Models\User;

use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $rules = ['required', 'integer', 'min:100000', 'max:999999'];

        $request->validate([
            '2fa_code_email' => $rules,
            '2fa_code' => $rules
        ]);
    }

    public function index()
    {

        $user = User::find(1);

        $code = rand(100000, 999999);

        $user->two_factor_code_email = Crypt::encryptString($code);

        $user->save();

        $antiFishingSecret = Crypt::decryptString($user->anti_fishing_secret);

        $user->notify(new EmailTwoFactorAuth($code, $antiFishingSecret, $user->preferred_lang));

        return view('welcome');
    }
}
