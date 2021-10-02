<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use App\Jobs\UpdateCredentialJob;

use Validator;

use App\Models\Slot;

use App\Services\CredentialService;

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
        'company_name' =>                       ['nullable', 'string', 'min:1', 'max:190'],
        'description' =>                        ['nullable', 'string', 'min:0', 'max:500'],
        'user_name' =>                          ['nullable', 'string', 'min:1', 'max:190'],
        'email' =>                              ['nullable', 'email', 'min:6', 'max:190'],
        'password' =>                           ['nullable', 'string', 'min:5', 'max:190'],
        'username' =>                           ['nullable', 'string', 'min:1', 'max:190'],
        'phone_number' =>                       ['nullable', 'string', 'min:8', 'max:190'],
        'security_question' =>                  ['nullable', 'string', 'min:5', 'max:190'],
        'security_answer' =>                    ['nullable', 'string', 'min:5', 'max:190'],
        'unique_code' =>                        ['nullable', 'string', 'min:1', 'max:190'],
        'multiple_codes' =>                     ['nullable', 'array', 'min:1'],
        'multiple_codes.*' =>                   ['nullable', 'string', 'min:1', 'max:25', 'distinct'],
        'crypto_codes' =>                       ['nullable', 'array', 'min:7'],
        'crypto_codes.*' =>                     ['nullable', 'string', 'min:2', 'max:25', 'distinct'],
        'accessing_device' =>                   ['required', 'string', 'min:1', 'max:190'],
        'accessing_platform' =>                 ['required', 'string', 'min:3', 'max:7', 'in:mobile,web,desktop']
    ];

    public function get_companies()
    {                                                   // 1 week
        $companies = cache()->remember('companies', 60 * 60 * 24 * 7, function () {
            return Company::select('name', 'url_logo', 'id')->get();
        });

        return response()->success([
            'companies' => $companies,
        ], 'success');
    }

    public function create(Request $request)
    {
        $data = $request->only(
            'company_name',
            'description',
            'user_name',
            'email',
            'password',
            'username',
            'phone_number',
            'security_question',
            'security_answer',
            'unique_code',
            'multiple_codes',
            'crypto_codes',
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

        if ($user->hasAnyRole(['free', 'semi-free'])) {
            if ($user->slots_available >= 1) {
                $user->slots_available = $user->slots_available - 1;
                $user->save();
            } else {
                return response()->error($data, 'api_messages.error.user_cant_create_credentials', 401);
            }
        }

        try {
            $credential = Slot::create([
                'user_id' => $user->id,
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
                (new CredentialService())->email_crud('create', $credential->id, $data['email']);
            }

            if (isset($data['password'])) {
                (new CredentialService())->password_crud('create', $credential->id, $data['password']);
            }

            if (isset($data['phone_number'])) {
                (new CredentialService())->phone_number_crud('create', $credential->id, $data['phone_number']);
            }

            if (isset($data['security_question']) && isset($data['security_answer'])) {
                (new CredentialService())->question_answer_crud('create', $credential->id, [
                    'question' => $data['security_question'],
                    'answer' => $data['security_answer'],
                ]);
            }

            if (isset($data['username'])) {
                (new CredentialService())->username_crud('create', $credential->id, $data['username']);
            }

            if (
                isset($data['unique_code'])
                ||
                isset($data['multiple_codes'])
                ||
                isset($data['crypto_codes'])
            ) {
                (new CredentialService())->security_code_crud('create', $credential->id, $data);
            }
        } catch (\Throwable $th) {
            return response()->error([
                'errors' => $th,
                'request' => $request->all(),
            ], 'api_messages.error.generic', 500);
        }

        UpdateCredentialJob::dispatch($credential->id)->delay(now()->addDays(10));

        $credential_created = Slot::with(
            'email',
            'password',
            'phone_number',
            'security_code',
            'security_question_answer',
            'username'
        )->find($credential->id);

        return response()->success(['credential' => $credential_created], 'credentials.created');
    }

    public function update(Request $request, $credential_id)
    {
        $data = $request->only(
            'company_name',
            'description',
            'user_name',
            'email',
            'password',
            'username',
            'phone_number',
            'security_question',
            'security_answer',
            'unique_code',
            'multiple_codes',
            'crypto_codes',
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

        try {

            $credential = Slot::where('user_id', $user->id)->where('id', $credential_id)->firstOrFail();

            $credential->user_name = isset($data['user_name']) ? Crypt::encryptString($data['user_name']) : null;
            $credential->char_count = isset($data['user_name']) ? strlen($data['user_name']) : null;
            $credential->description = isset($data['description']) ? $data['description'] : '';

            if (isset($data['email'])) {
                (new CredentialService())->email_crud('update', $credential->id, $data['email']);
            } else {
                (new CredentialService())->email_crud('delete', $credential->id);
            }

            if (isset($data['password'])) {
                (new CredentialService())->password_crud('update', $credential->id, $data['password']);
            } else {
                (new CredentialService())->password_crud('delete', $credential->id);
            }

            if (isset($data['phone_number'])) {
                (new CredentialService())->phone_number_crud('update', $credential->id, $data['phone_number']);
            } else {
                (new CredentialService())->phone_number_crud('delete', $credential->id);
            }

            if (isset($data['security_question']) && isset($data['security_answer'])) {
                (new CredentialService())->question_answer_crud('update', $credential->id, [
                    'question' => $data['security_question'],
                    'answer' => $data['security_answer'],
                ]);
            } else {
                (new CredentialService())->question_answer_crud('delete', $credential->id);
            }

            if (isset($data['username'])) {
                (new CredentialService())->username_crud('update', $credential->id, $data['username']);
            } else {
            }

            (new CredentialService())->security_code_crud('update or delete', $credential->id, $data);
        } catch (\Throwable $th) {
            return response()->error([
                'errors' => $th,
                'request' => $request->all(),
            ], 'api_messages.error.generic', 500);
        }

        UpdateCredentialJob::dispatch($credential->id)->delay(now()->addDays(10));

        $credential_updated = Slot::with(
            'email',
            'password',
            'phone_number',
            'security_code',
            'security_question_answer',
            'username'
        )->find($credential->id);

        return response()->success(['credential' => $credential_updated], 'success');
    }

    public function delete(Request $request, $credential_id)
    {
        $user = $request->user();

        try {
            $credential = Slot::where('user_id', $user->id)->where('id', $credential_id)->firstOrFail();

            $credential->delete();
        } catch (\Throwable $th) {
            return response()->error([
                'errors' => $th,
                'request' => $request->all()
            ], 'api_messages.error.generic', 404);
        }

        if ($user->hasAnyRole(['free', 'semi-premium'])) {
            $user->slots_available += 1;
            $user->save();
        }

        return response()->success([], 'success');
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

        $credentials = Slot::where('user_id', $user->id)->select(
            'company_name',
            'last_seen',
            'id',
            'accessing_device',
            'accessing_platform',
            'created_at',
            'updated_at'
        )->get();

        return response()->success(['recently_seen' => $credentials], 'success');
    }

    /**************************************************************************************************************** helper functions */
    private function fuse_strings($array_of_strings)
    {
        $string = '';

        foreach ($array_of_strings as $single_string) {
            $string = $string . ' ' . $single_string;
        }

        return substr($string, 0, 1);
    }
}
