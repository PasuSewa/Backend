<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\EmailTwoFactorAuth;
use App\Models\User;

use Illuminate\Support\Facades\Crypt;

use PragmaRX\Google2FA\Google2FA;

use Auth;

class AuthController extends Controller
{
    public function admin_login(Request $request)
    {
        $rules = ['required', 'integer', 'min:100000', 'max:999999'];

        $data = $request->validate([
            '2fa_code_email' => $rules,
            '2fa_code' => $rules,
            'anti_fishing_secret' => ['required', 'string', 'min:4', 'max:190']
        ]);

        $google2fa = new Google2FA();

        try {
            $user = User::findOrFail(1); // only the admin can pass this authentication

            $valid_g2fa = $google2fa->verifyKey(Crypt::decryptString($user->two_factor_secret), $data['2fa_code'], 0);

            $valid_email_code = $data['2fa_code_email'] === Crypt::decryptString($user->two_factor_code_email);

            $valid_secret_anti_fishing = $data['anti_fishing_secret'] === Crypt::decryptString($user->anti_fishing_secret);
        } catch (\Throwable $th) {
            return response()->view("errors.500");
        }

        if (!$valid_email_code || !$valid_g2fa || !$valid_secret_anti_fishing) {
            return back()->withError('At least one of the credentials was incorrect.');
        } elseif (Auth::loginUsingId(1)) {
            request()->session()->regenerate();

            $user->two_factor_code_email = null;

            $user->save();

            return redirect()->route('home');
        }
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('welcome');
    }
}
