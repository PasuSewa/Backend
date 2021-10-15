<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UpdateCredentialJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;

use Validator;

use App\Models\User;

use App\Notifications\EmailTwoFactorAuth;

use App\Services\AuthService;

class AuthController extends Controller
{
    /**
     * Send Code By Email
     * 
     * This method will send the security code needed for the user to login by the "login with email code" method.
     * 
     * Remember that the email parameter must exists either on the "emails" or in "recovery_emails" columns
     * 
     * @group Auth
     * 
     * @header Accept-Language es | en | jp
     * 
     * @response {
     *      "status": 200,
     *      "message": "Succes!",
     *      "data": {}
     * }
     * 
     * @response status=401 scenario="validation failed" {
     *      "status": 401,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "email": "The field "email" must be a valid email."
     *              }
     *          ],
     *          "request": {
     *              "isSeconadry": false,
     *              "email": "fake_email.com",
     *          }
     *      }
     * }
     * 
     * @response status=404 scenario="user with email = user-email was not found" {
     *      "status": 404,
     *      "errors": [
     *          {
     *              "message": "The user was not found.",
     *          }
     *      ],
     *      "message": "The user was not found.",
     * }
     */
    public function send_code_by_email(Request $request)
    {
        $data = $request->only('email', 'isSecondary');

        $validation = Validator::make($data, [
            'email' => ['required', 'email', 'min:5', 'max:190'],
            'isSecondary' => ['required', 'boolean'],
        ]);

        if ($validation->fails()) {
            return (new AuthService())->validation_error($request, $validation);
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
            return response()->error($data, 'api_messages.error.user_was_not_found_or_isnt_allowed', 404);
        }

        $code = rand(100000, 999999);

        if ($data['isSecondary']) {
            $user->two_factor_code_recovery = Crypt::encryptString($code);
        } else {
            $user->two_factor_code_email = Crypt::encryptString($code);
        }
        $user->save();

        try {
            $anti_fishing_secret = Crypt::decryptString($user->anti_fishing_secret);

            if ($data['isSecondary']) {
                Notification::route(
                    'mail',
                    $user->recovery_email
                )->notify(new EmailTwoFactorAuth(
                    $code,
                    $anti_fishing_secret,
                    $user->preferred_lang
                ));
            } else {
                $user->notify(new EmailTwoFactorAuth($code, $anti_fishing_secret, $user->preferred_lang));
            }
        } catch (\Throwable $th) {
            $data = [
                'errors' => [
                    'exception' => $th
                ]
            ];
            return response()->error($data, 'api_messages.error.generic', 500);
        }

        return response()->success([], 'auth.email_sent');
    }

    /**************************************************************************************************************** register process */
    /**
     * Register, Step One
     * 
     * The first step in order to register a new user. This method stores the user's accessing information, and dispatches an event to send security codes
     * to both, the main and the recovery, emails.
     * 
     * @group Register
     * 
     * @header Accept-Language es | en | jp
     * 
     * @response {
     *      "status": 200,
     *      "message": "success!",
     *      "data": {
     *          "registered_email": "main_email@email_company.com"
     *      }
     * }
     * 
     * @response status=401 scenario="validation failed" {
     *      "status": 401,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "mainEmail": "The field "mainEmail" must be a valid email."
     *              }
     *          ],
     *          "request": {
     *              "name": "user's name",
     *              "phoneNumber": "+1 555-1234-5678",
     *              "mainEmail": "fake_email.com",
     *              "recoveryEmail": "fake_mail@email_company.com",
     *              "secretAntiFishing": "secret",
     *              "secretAntiFishing_confirmation": "secret",
     *              "invitationCode": "AAAAAAAAAA"
     *          }
     *      }
     * }
     */
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
            return (new AuthService())->validation_error($request, $validation);
        }

        $slots_available = isset($data['invitationCode']) ? (new AuthService())->spend_invitation_code($data['invitationCode']) : 5;

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

    /**
     * Register, Step Two
     * 
     * This method will receive both of the security codes that were sent to the user on the previous step, and verify that both of them are correct.
     * 
     * @group Register
     * 
     * @header Accept-Language es | en | jp
     * 
     * @bodyParam mainEmailCode int required Must be a number of 6 digits.
     * @bodyParam recoveryEmailCode int required Must be a number of 6 digits.
     * @bodyParam MainEmail string required The email must already exist on the database.
     * 
     * @response {
     *      "status": 200,
     *      "message": "success!",
     *      "data": {
     *          "token": "the authorization token needed for the third step."
     *      }
     * }
     * 
     * @response status=401 scenario="validation failed" {
     *      "status": 401,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "mainEmail": "The field "mainEmail" must be a valid email."
     *              }
     *          ],
     *          "request": {
     *              "mainEmail": "fake_email.com",
     *              "mainEmailCode": 123456,
     *              "recoveryEmailCode": 123456,
     *          }
     *      }
     * }
     */
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
            return (new AuthService())->validation_error($request, $validation);
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
            return (new AuthService())->validation_error($request);
        }

        $user->two_factor_code_email = null;
        $user->two_factor_code_recovery = null;
        $user->two_factor_secret = (new AuthService())->generate_2fa_secret();
        $user->save();

        return response()->success(['token' => auth('api')->setTTL(7200)->tokenById($user->id)], 'auth.email_verified');
    }

    /**
     * Register, Step Three
     * 
     * This method will invalidate the auth token sent with the request.
     * 
     * <aside class="notice">This method is also used to verify the 2FA code if the user decides to renew it.</aside>
     * 
     * @group Register
     * 
     * @header Accept-Language es | en | jp
     * 
     * @authenticated
     * 
     * @bodyParam twoFactorCode int required Must be a 6 digits number.
     * 
     * @response {
     *      "status": 200,
     *      "message": "success!",
     *      "data": {
     *          "user_data": {
     *              "id": 2,
     *              "name": "user's name",
     *              "email": "email@email_company.com",
     *              "recovery_email": "second_email@email.company",
     *              "slots_available": 5,
     *              "invitation_code": "AAAAAAAAAA",
     *              "role": "free",
     *              "preferred_lang": "jp",
     *          },
     *          "user_credentials": [],
     *          "token": "the auth token renewed"
     *      }
     * }
     * 
     * @response status=401 scenario="validation failed" {
     *      "status": 401,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "twoFactorCode": "The field "twoFactorCode" must be an integer."
     *              }
     *          ],
     *          "request": {
     *              "twoFactorCode": "123456",
     *          }
     *      }
     * }
     */
    public function verify_2fa(Request $request)
    {
        $data = $request->only('twoFactorCode', 'isForMobile');

        $user = $request->user();

        $validation = Validator::make($data, [
            'twoFactorCode' => ['required', 'integer', 'min:000000', 'max:999999'],
            'isForMobile' => ['nullable', 'boolean']
        ]);

        if ($validation->fails()) {
            return (new AuthService())->validation_error($request, $validation);
        }

        $is_valid = (new AuthService())->validate_2fa_code($user->two_factor_secret, $data['twoFactorCode']);

        if ($is_valid) {
            //in the react app, the token its not stored anywhere, so we need to destroy the old session
            //in order to give the frontend another token
            auth('api')->invalidate();

            $mobile = isset($data['isForMobile']) && $data['isForMobile'];

            return response()->user_was_authenticated(['user' => $user], '2fa_code_is_correct', true, false, $mobile);
        } else {
            return (new AuthService())->validation_error($request, null, 'api_messages.error.2fa_code_invalid');
        }
    }

    /**************************************************************************************************************** login options */
    /**
     * Login By 2 Factor Code (TOTP)
     * 
     * This is the method to login using the user's main email, and the time-based one-time-password (the code generated in apps like Google Authenticator).
     * 
     * <aside class="notice">This method will return an array of Credentials, you can see the Credential interface on the README file.</aside>
     * 
     * @group Login
     * 
     * @header Accept-Language es | en | jp
     * 
     * @bodyParam email string required The main email of the user.
     * @bodyParam twoFactorCode int required Must be a 6 digits number.
     * @bodyParam isForMobile boolean If you're sending a request from a desktop client or a mobile app, you should set this to "true" to get a JWT token 
     * that won't have any expiration date.
     * 
     * @response {
     *      "status": 200,
     *      "message": "success!",
     *      "data": {
     *          "user_data": {
     *              "id": 2,
     *              "name": "user's name",
     *              "email": "email@email_company.com",
     *              "recovery_email": "second_email@email.company",
     *              "slots_available": 5,
     *              "invitation_code": "AAAAAAAAAA",
     *              "role": "free",
     *              "preferred_lang": "jp",
     *          },
     *          "user_credentials": [],
     *          "token": "the auth token"
     *      }
     * }
     * 
     * @response status=401 scenario="validation failed" {
     *      "status": 401,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "twoFactorCode": "The field "twoFactorCode" must be an integer."
     *              }
     *          ],
     *          "request": {
     *              "twoFactorCode": "123456",
     *              "email": "email@email_company.com"
     *          }
     *      }
     * }
     */
    public function login_by_g2fa(Request $request)
    {
        $data = $request->only('email', 'twoFactorCode', 'isForMobile');

        $validation = Validator::make($data, [
            'email' => ['required', 'email', 'min:3', 'max:190', 'exists:users,email'],
            'twoFactorCode' => ['required', 'integer', 'min:000000', 'max:999999'],
            'isForMobile' => ['nullable', 'boolean'],
        ]);

        if ($validation->fails()) {
            return (new AuthService())->validation_error($request, $validation);
        }

        $user = User::where('email', $data['email'])->firstOrFail();

        $is_valid = (new AuthService())->validate_2fa_code($user->two_factor_secret, $data['twoFactorCode']);

        $mobile = isset($data['isForMobile']) && $data['isForMobile'];

        if ($is_valid) {
            return response()->user_was_authenticated(['user' => $user], '2fa_code_is_correct', true, true, $mobile);
        } else {
            return (new AuthService())->validation_error($request, null, 'api_messages.error.2fa_code_invalid');
        }
    }

    /**
     * Login By Email Code
     * 
     * In order for this method to work, the user must already have the correct security code in the database. If the user asked to send an email code to their 
     * recovery email, they won't be able to login by this method, if they're using their main email on this request.
     * 
     * <aside class="notice">This method will return an array of Credentials, you can see the Credential interface on the README file.</aside>
     * 
     * @group Login
     * 
     * @header Accept-Language es | en | jp
     * 
     * @bodyParam mainEmail string required The main email of the user.
     * @bodyParam recoveryEmail string Is required only if the user has sent the security code to their recovery email.
     * @bodyParam code int required Must be a 6 digits number.
     * @bodyParam isForMobile boolean If you're sending a request from a desktop client or a mobile app, you should set this to "true" to get a JWT token 
     * that won't have any expiration date.
     * 
     * @response {
     *      "status": 200,
     *      "message": "success!",
     *      "data": {
     *          "user_data": {
     *              "id": 2,
     *              "name": "user's name",
     *              "email": "email@email_company.com",
     *              "recovery_email": "second_email@email.company",
     *              "slots_available": 5,
     *              "invitation_code": "AAAAAAAAAA",
     *              "role": "free",
     *              "preferred_lang": "jp",
     *          },
     *          "user_credentials": [],
     *          "token": "the auth token"
     *      }
     * }
     * 
     * @response status=401 scenario="validation failed" {
     *      "status": 401,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "code": "The field "code" must be an integer."
     *              }
     *          ],
     *          "request": {
     *              "code": "123456",
     *              "mainEmail": "email@email_company.com",
     *          }
     *      }
     * }
     */
    public function login_by_email_code(Request $request)
    {
        $data = $request->only('mainEmail', 'recoveryEmail', 'code', 'isForMobile');

        $validation = Validator::make($data, [
            'mainEmail' => ['required', 'email', 'min:3', 'max:190', 'exists:users,email'],
            'recoveryEmail' => ['nullable', 'email', 'min:3', 'max:190', 'exists:users,recovery_email'],
            'code' => ['required', 'integer', 'min:000000', 'max:999999'],
            'isForMobile' => ['nullable', 'boolean']
        ]);

        if ($validation->fails()) {
            return (new AuthService())->validation_error($request, $validation);
        }

        $user = User::where('email', $data['mainEmail'])->firstOrFail();

        $db_code = !is_null($data['recoveryEmail']) ? $user->two_factor_code_recovery : $user->two_factor_code_email;

        $valid_email_code = $data['code'] === Crypt::decryptString($db_code);

        if ($valid_email_code) {

            $mobile = isset($data['isForMobile']) && $data['isForMobile'];

            return response()->user_was_authenticated(['user' => $user], '2fa_code_is_correct', true, true, $mobile);
        } else {
            return (new AuthService())->validation_error($request);
        }
    }

    /**
     * Login By Security Code
     * 
     * This is the only method that does not require 2-factor-authentication.
     * 
     * <aside class="notice">This method will return an array of Credentials, you can see the Credential interface on the README file.</aside>
     * 
     * @group Login
     * 
     * @header Accept-Language es | en | jp
     * 
     * @bodyParam mainEmail string required
     * @bodyParam recoveryEmail string required
     * @bodyParam antiFishingSecret string required
     * @bodyParam securityCode string required All users have one 10-character recovery code for logging into ther account if they aren't able
     *  to use any 2-factor-authentication
     * 
     * @response {
     *      "status": 200,
     *      "message": "success!",
     *      "data": {
     *          "user_data": {
     *              "id": 2,
     *              "name": "user's name",
     *              "email": "email@email_company.com",
     *              "recovery_email": "second_email@email.company",
     *              "slots_available": 5,
     *              "invitation_code": "AAAAAAAAAA",
     *              "role": "free",
     *              "preferred_lang": "jp",
     *          },
     *          "user_credentials": [],
     *          "token": "the auth token"
     *      }
     * }
     * 
     * @response status=401 scenario="validation failed" {
     *      "status": 401,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "securityCode": "The field "securityCode" must have at least 10 characters."
     *              }
     *          ],
     *          "request": {
     *              "mainEmail": "email@email_company.com",
     *              "recoveryEmail": "second@email.com",
     *              "antiFishingSecret": "secret",
     *              "securityCode": 123456,
     *          }
     *      }
     * }
     */
    public function login_by_security_code(Request $request)
    {
        $data = $request->only('mainEmail', 'recoveryEmail', 'antiFishingSecret', 'securityCode', 'isForMobile');

        $validation = Validator::make($data, [
            'mainEmail' => ['required', 'email', 'min:3', 'max:190', 'exists:users,email'],
            'recoveryEmail' => ['required', 'email', 'min:3', 'max:190'],
            'antiFishingSecret' => ['required', 'string', 'min:5', 'max:190'],
            'securityCode' => ['required', 'string', 'min:10', 'max:10']
        ]);

        if ($validation->fails()) {
            return (new AuthService())->validation_error($request, $validation);
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

            $mobile = isset($data['isForMobile']) && $data['isForMobile'];

            return response()->user_was_authenticated(['user' => $user], '2fa_code_is_correct', true, true, $mobile);
        } else {
            return (new AuthService())->validation_error($request);
        }
    }

    /**
     * Logout
     * 
     * This method does not require anything, except for a valid authentication token.
     * 
     * @authenticated
     * 
     * @group Login
     * 
     * @header Accept-Language es | en | jp
     * 
     * @response {
     *      "status": 200,
     *      "message": "Succes!",
     *      "data": {}
     * }
     * 
     */
    public function logout()
    {
        auth('api')->invalidate();

        return response()->success([], 'auth.logged_out');
    }

    /**************************************************************************************************************** access encrypted data */
    /**
     * Grant Access to Confidential Information
     * 
     * This method is used to access the encrypted data of a Credential, or the encrypted data of the user itself.
     * 
     * <aside class="notice">This method may return a decrypted Credential, you can see the Credential interface on the README file.</aside>
     * 
     * @authenticated
     * 
     * @group Auth
     * 
     * @header Accept-Language es | en | jp
     * 
     * @bodyParam accessTo string required Must be either one of these exact two options: "user-data" or "credential-data".
     * @bodyParam credentialId integer Its required, only if "accessTo" is equal to "credential-data".
     * @bodyParam accessingDevice string required The user agent of the navigator, or the unique ID of the device. (min: 1, max: 190 char).
     * @bodyParam accessingPlattform string required Must be one of these exact three options: "web", "desktop", "mobile".
     * 
     * @response status=200 scenario="access to credential data" {
     *      "status": 200,
     *      "message": "Success!"
     *      "data": {
     *          "id": 1,
     *          "user_id": 1,
     *          "company_id": null,
     *          "company_name": "company's name",
     *          "email": "main@email.com",
     *          "password": "my_secret_password1234",
     *          "last_seen": "2021-12-23 10:05:31",
     *          "created_at": "2021-12-23 10:05:31",
     *          "updated_at": "2021-12-23 10:05:31",
     *      },
     *  }
     * 
     * @response status=200 scenario="access to user data" {
     *      "status": 200,
     *      "message": "Success!"
     *      "data": {
     *          "name": "jhon doe",
     *          "email": "main@email.com",
     *          "recovery_email": "recovery@email.com",
     *          "phone_number": "+1 555-1234-5678",
     *          "anti_fishing_secret": "secret",
     *          "security_access_code": "AAAAAAAAAA"
     *      }
     * }
     * 
     * @response status=401 scenario="validation failed" {
     *      "status": 401,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "accessTo": "The field "accessTo" must be "credential-data" or "user-data"."
     *              }
     *          ],
     *          "request": {
     *              "accessTo": "encrypted-data",
     *              "accessingDevice": "Windows NT 6.1; Win64; x64; rv:47.0",
     *              "accessingPlatform": "web",
     *          }
     *      }
     * }
     */
    public function grant_access(Request $request)
    {
        $data = $request->only('accessTo', 'credentialId', 'accessingDevice', 'accessingPlatform');

        $validation = Validator::make($data, [
            'accessTo' => ['required', 'string', 'max:190', 'in:user-data,credential-data'],
            'credentialId' => ['nullable', 'integer', 'min:1', 'exists:slots,id'],
            'accessingDevice' => ['required', 'string', 'min:1', 'max:190'],
            'accessingPlatform' => ['required', 'string', 'min:3', 'max:7', 'in:mobile,web,desktop']
        ]);

        if ($validation->fails()) {
            return (new AuthService())->validation_error($request, $validation);
        }

        $user = $request->user();

        if ($data['accessTo'] === 'user-data') {
            $data = (new AuthService())->access_user_data($user);

            return response()->success($data, 'auth.access_granted');
        }

        if ($data['accessTo'] === 'credential-data') {
            $decrypted_credential = (new AuthService())->access_credential_data([
                'user_id' => $user->id,
                'credential_id' => $data['credentialId'],
                'user_agent' => $data['accessingDevice'],
                'accessing_platform' => $data['accessingPlatform']
            ]);

            UpdateCredentialJob::dispatch($data['credentialId'])->delay(now()->addDays(10));

            return response()->success(['decrypted_credential' => $decrypted_credential], 'succes');
        }
    }

    /**
     * Refresh 2-Factor-Authentication Secret
     * 
     * This method is responsible for updating and returning the secret key for the time-based one-time-password 
     * (the code generated in apps like Google Authenticator). In order to check if the user has set up correctly the new secret key, you can verify it
     * using the endpoint of Register, Third Step.
     * 
     * <aside class="notice">You can generate a QR-code for the user to scan using the structure given in the README file.</aside>
     * 
     * @group Auth
     * 
     * @header Accept-Language es | en | jp
     * 
     * @authenticated
     * 
     * @response {
     *      "status": 200,
     *      "message": "Succes!",
     *      "data": {
     *          "secret": "AAAAAAAAAA",
     *          "email": "main@email.com",
     *      },
     * }
     */
    public function refresh_2fa_secret(Request $request)
    {
        $user = $request->user();

        $secret = (new AuthService())->generate_2fa_secret(true);

        $user->two_factor_secret = Crypt::encryptString($secret);
        $user->save();

        return response()->success(['secret' => $secret, 'email' => $user->email], 'auth.refresh_2fa_secret');
    }

    /**
     * Renew Security Access Code
     * 
     * This method is used to generate a new security access code for the user. This code can be used to login in the case that the user has lost access to all
     * 2-factor-authentication methods.
     * 
     * @group Auth
     * 
     * @authenticated 
     * 
     * @header Accept-Language es | en | jp
     * 
     * @response {
     *      "status": 200,
     *      "message": "Succes!",
     *      "data": {
     *          "renewed_code": "AAAAAAAAAA",
     *      },
     * }
     */
    public function renew_security_code(Request $request)
    {
        $user = $request->user();
        $renewed_code = strtoupper(Str::random(10));
        $user->recovery_code = Crypt::encryptString($renewed_code);
        $user->save();

        return response()->success(['renewed_code' => $renewed_code], 'success');
    }

    /**
     * Verify Auth Token
     * 
     * Since this route requires authentication, there is no need to add any logic to this method.
     * If the token isn't actually valid, the user won't be able to reach this point.
     * 
     * @group Auth
     * 
     * @authenticated 
     * 
     * @header Accept-Language es | en | jp
     * 
     * @response {
     *      "status": 200,
     *      "message": "Succes!",
     *      "data": {},
     * }
     */
    public function verify_token(Request $request)
    {
        return response()->success([], 'success');
    }
}
