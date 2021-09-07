<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Validator;
use Auth;

use App\Models\User;

use Illuminate\Support\Facades\Crypt;

use PragmaRX\Google2FA\Google2FA;

use App\Notifications\EmailTwoFactorAuth;

class AuthController extends Controller
{
    private $name_rules = [
        'string',
        'min:5',
        'max:100',
        'required',
    ];
    private $phone_number_rules = [
        'string',
        'min:6',
        'max:20',
        'required',
    ];
    private $main_email_rules = [
        'email',
        'min:5',
        'max:190',
        'unique:users,email',
        'unique:users,recovery_email',
        'required',
    ];
    private $second_email_rules = [
        'email',
        'min:5',
        'max:190',
        'unique:users,email',
        'unique:users,recovery_email',
        'required',
    ];
    private $anti_fishing_rules = [
        'string',
        'min:5',
        'max:190',
        'required',
        'confirmed'
    ];
    private $anti_fishing_rules_confirmation = [
        'string',
        'min:5',
        'max:190',
        'required',
    ];
    private $invitation_code_rules = [
        'string',
        'min:10',
        'max:10',
        'exists:users,invitation_code',
        'nullable'
    ];

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
                'message' => __('api_messages.error.generic'),
                'errors' => [
                    'exception' => $th
                ]
            ], 500);
        }
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
            'name' => $this->name_rules,
            'phoneNumber' => $this->phone_number_rules,
            'mainEmail' => $this->main_email_rules,
            'secondaryEmail' => $this->second_email_rules,
            'secretAntiFishing' => $this->anti_fishing_rules,
            'secretAntiFishing_confirmation' => $this->anti_fishing_rules_confirmation,
            'invitationCode' => $this->invitation_code_rules
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 418,
                'errors' => $validation->errors(),
                'request' => $request->all(),
                'message' => __('api_messages.error.validation'),
            ], 418);
        }

        $slots_available = $data['invitationCode'] ? $this->spend_invitation_code($data['invitationCode']) : 5;

        $google2fa = new Google2FA();
        $two_factor_secret = $google2fa->generateSecretKey();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['mainEmail'],
            'recovery_email' => $data['secondaryEmail'],
            'phone_number' => Crypt::encryptString($data['phoneNumber']),
            'anti_fishing_secret' => Crypt::encryptString($data['secretAntiFishing']),
            'slots_available' =>  $slots_available,
            'invitation_code' => strtoupper(Str::random(15)),
            'two_factor_secret' => Crypt::encryptString($two_factor_secret),
            'two_factor_code_email' => Crypt::encryptString(rand(100000, 999999)),
            'two_factor_code_recovery' => Crypt::encryptString(rand(100000, 999999)),
            'preferred_lang' => app()->getLocale()
        ]);

        return response()->json([
            'status' => 200,
            'registered_main_email' => $user->email,
            'token' => auth('api')->tokenById($user->id),
            'message' => __('api_messages.success.auth.user_created')
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
                'status' => 418,
                'errors' => $validation->errors(),
                'request' => $request->all(),
                'message' => __('api_messages.error.validation'),
            ], 418);
        }

        // try {
        $user = User::where('email', $data['mainEmail'])->firstOrFail();

        $main_code = Crypt::decryptString($user->two_factor_code_email);
        $second_code = Crypt::decryptString($user->two_factor_code_recovery);

        $main_is_correct = $main_code === $data['mainEmailCode'];
        $second_is_correct = $second_code === $data['recoveryEmailCode'];

        if (!$main_is_correct || !$second_is_correct) {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'error_message' => __('api_messages.error.validation')
                ],
                'message' => __('api_messages.error.validation'),
            ], 400);
        }

        $user->two_factor_code_email = null;
        $user->two_factor_code_recovery = null;
        $user->save();

        if (Auth::loginUsingId($user->id)) {

            $auth_user = Auth::user();

            return response()->json([
                'status' => 200,
                'message' => __('api_messages.success.auth.email_verified'),
                'auth_token' => $auth_user->createToken('Auth Token')->accessToken
            ], 200);
        } else {
            return response()->json([
                'message' => 'Las credenciales son incorrectas',
                'status' => 401
            ], 401);
        }
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'message' => __('api_messages.error.generic'),
        //         'errors' => [
        //             'exception' => $th
        //         ]
        //     ], 500);
        // }
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

    public function generate_2fa_secret()
    {
        // ya que no se puede mostrar la secret key bajo ninguna circunstancia, voy a tener que hacer otro componente
        // que genere una nueva secret key...
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

    public function get_user(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ], 200);
    }
}
