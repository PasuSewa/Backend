<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use Validator;

use App\Models\Slot;
use App\Models\Username;
use App\Models\Email;
use App\Models\Password;
use App\Models\PhoneNumber;
use App\Models\QuestionAnswer;
use App\Models\SecurityCode;

class CredentialController extends Controller
{
    /**
     * Note: All of these methods will only return public data.
     * 
     * The decripted credentials are obtained from AuthController, in "grant_access" function
     */

    // used for "create" & "update"
    private $validation_rules = [
        'id' =>                                 ['nullable', 'integer', 'exists:slots,id'],
        'user_id' =>                            ['nullable', 'integer', 'exists:users,id'],
        'company_id' =>                         ['nullable', 'integer', 'exists:companies,id'],
        'company_name' =>                       ['nullable', 'string', 'min:1', 'max:190'],
        'description' =>                        ['required', 'string', 'min:0', 'max:500'],
        'user_name' =>                          ['nullable', 'string', 'min:1', 'max:190'],
        'email' =>                              ['nullable', 'email', 'min:6', 'max:190'],
        'password' =>                           ['nullable', 'string', 'min:5', 'max:190'],
        'username' =>                           ['nullable', 'string', 'min:1', 'max:190'],
        'phone_number' =>                       ['nullable', 'string', 'min:8', 'max:190'],
        'security_question' =>                  ['nullable', 'string', 'min:5', 'max:190'],
        'security_answer' =>                    ['nullable', 'string', 'min:5', 'max:190'],
        'unique_security_code' =>               ['nullable', 'string', 'min:1', 'max:190'],
        'multiple_security_code' =>             ['nullable', 'array', 'min:1'],
        'multiple_security_code.*' =>           ['nullable', 'string', 'min:1', 'max:25', 'distinct'],
        'crypto_currency_access_codes' =>       ['nullable', 'array', 'min:7'],
        'crypto_currency_access_codes.*' =>     ['nullable', 'string', 'min:2', 'max:25', 'distinct'],
        'accessing_device' =>                   ['required', 'string', 'min:1', 'max:190'],
        'accessing_platform' =>                ['required', 'string', 'min:3', 'max:7', 'in:mobile,web,desktop']
    ];

    public function index(Request $request)
    {
        $user = $request->user();

        /**
         * to do:
         * 
         * 1- return all credentials for the user
         */
    }

    public function create(Request $request)
    {
        $data = $request->only(
            'company_id',
            'company_name',
            'description',
            'user_name',
            'email',
            'password',
            'username',
            'phone_number',
            'security_question',
            'security_answer',
            'unique_security_code',
            'multiple_security_code',
            'crypto_currency_access_codes',
            'accessing_device',
            'accessing_platform',
        );

        $validation = Validator::make($data, $this->validation_rules);

        if ($validation->fails()) {
            $data = [
                'errors' => $validation->errors(),
                'request' => $request->all(),
            ];

            return response()->error($data, 'api_messages.error.parameter_was_incorrect', 401);
        }

        $user = $request->user();
        $user->slots_available = $user->slots_available - 1;
        $user->save();

        try {
            $credential = Slot::create([
                'user_id' => $user->id,
                'company_id' => isset($data['company_id']) ? $data['company_id'] : 1, // placeholder
                'company_name' => isset($data['company_name']) && !isset($data['company_id']) ? $data['company_name'] : null,
                'last_seen' => now()->format('Y-m-d H:i:s'),
                'recently_seen' => true,
                'accessing_device' => $data['accessing_device'],
                'accessing_platform' => $data['accessing_platform'],
                'user_name' => isset($data['user_name']) ? Crypt::encryptString($data['user_name']) : null,
                'char_count' => isset($data['user_name']) ? strlen($data['user_name']) : null,
                'description' => isset($data['description']) ? $data['description'] : '',
            ]);

            if (isset($data['email'])) {
                Email::create([
                    'slot_id' => $credential->id,
                    'email' => Crypt::encryptString($data['email']),
                    'opening' => substr($data['email'], 0, 2),
                    'ending' => explode('@', $data['email'], 2)[1],
                    'char_count' => strlen($data['email']),
                ]);
            }

            if (isset($data['password'])) {
                Password::create([
                    'slot_id' => $credential->id,
                    'password' => Crypt::encryptString($data['password']),
                    'char_count' => strlen($data['password'])
                ]);
            }

            if (isset($data['phone_number'])) {
                PhoneNumber::create([
                    'slot_id' => $credential->id,
                    'phone_number' => Crypt::encryptString($data['phone_number']),
                    'opening' => substr($data['phone_number'], 0, 3),
                    'char_count' => strlen($data['phone_number']) - 5,
                    'ending' => substr($data['phone_number'], -2)
                ]);
            }

            if (isset($data['security_question']) && isset($data['security_answer'])) {
                QuestionAnswer::create([
                    'slot_id' => $credential->id,
                    'security_question' => Crypt::encryptString($data['security_question']),
                    'security_answer' => Crypt::encryptString($data['security_answer']),
                ]);
            }

            if (isset($data['username'])) {
                Username::create([
                    'slot_id' => $credential->id,
                    'username' => Crypt::encryptString($data['username']),
                    'char_count' => strlen($data['username']),
                ]);
            }

            if (
                isset($data['unique_security_code'])
                ||
                isset($data['multiple_security_code'])
                ||
                isset($data['crypto_currency_access_codes'])
            ) {
                SecurityCode::create([
                    'slot_id' => $credential->id,
                    'unique_code' =>
                    isset($data['unique_security_code'])
                        ?
                        Crypt::encryptString($data['unique_security_code'])
                        :
                        null,
                    'multiple_codes' =>
                    isset($data['multiple_security_code'])
                        ?
                        Crypt::encryptString($this->fuse_strings($data['multiple_security_code']))
                        :
                        null,
                    'multiple_codes_length' =>
                    isset($data['multiple_security_code'])
                        ?
                        count($data['multiple_security_code'])
                        :
                        null,
                    'crypto_codes' =>
                    isset($data['crypto_currency_access_codes'])
                        ?
                        Crypt::encryptString($this->fuse_strings($data['crypto_currency_access_codes']))
                        :
                        null,
                    'crypto_codes_length' =>
                    isset($data['crypto_currency_access_codes'])
                        ?
                        count($data['crypto_currency_access_codes'])
                        :
                        null
                ]);
            }
        } catch (\Throwable $th) {
            return response()->error([
                'errors' => $th,
                'request' => $request->all(),
            ], 'api_messages.error.generic', 500);
        }

        return response()->success(['credential_id' => $credential->id], 'credentials.created');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        /**
         * to do:
         * 
         * 1- validate request must have the following:
         * 
         * 2- make use of multiple "if" statements to check if credential has set the correct values for each property
         * in case of !isset($credential_property) it must be deleted from db
         * 
         * 3- save all changes (don't forget to encrypt the data)
         */
    }

    public function delete(Request $request, $credential_id)
    {
        $user = $request->user();

        /**
         * to do:
         * 
         * 1- delete the credential (this should also delete all other properties related on the db)
         */
    }

    public function find(Request $request, $credential_id)
    {
        $user = $request->user();

        /**
         * to do:
         * 
         * 1- find the credential if exists
         */
    }

    public function get_recently_seen(Request $request)
    {
        $user = $request->user();

        /**
         * to do:
         * 
         * 1- get all credentials with "recently_seen" property equal to "true", and sort them by "last_seen" propertty
         */
    }

    private function fuse_strings($array_of_strings)
    {
        $string = '';

        foreach ($array_of_strings as $single_string) {
            $string = $string . ' ' . $single_string;
        }

        return substr($string, 0, 1);
    }
}
