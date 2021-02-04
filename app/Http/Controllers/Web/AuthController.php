<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\EmailTwoFactorAuth;
use App\Models\User;

use Illuminate\Support\Facades\Crypt;

use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $rules = ['required', 'integer', 'min:100000', 'max:999999'];

        $data = $request->validate([
            '2fa_code_email' => $rules,
            '2fa_code' => $rules
        ]);

        $google2fa = new Google2FA();

        $user = User::find(1);

        $validG2FA = $google2fa->verifyKey(Crypt::decryptString($user->two_factor_secret), $data['2fa_code'], 0);

        $validEmail2FA = $data['2fa_code_email'] === Crypt::decryptString($user->two_factor_code_email);

        if ($validEmail2FA && $validG2FA) 
        {
            dd('logged in!');
        } else 
        {
            return back()->withError('At least one of the codes has failed the validation.');
        }
    }
}
