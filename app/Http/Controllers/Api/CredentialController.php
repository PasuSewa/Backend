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

    /**
     * Get Companies
     * 
     * This is the method used to get all the available options for companies.
     * 
     * <aside class="notice">This method stores the results in cache for 1 week, so no change will take place until that time.</aside>
     * 
     * @group Companies
     * 
     * @response {
     *      "status": 200,
     *      "message": "Success!",
     *      "data": {
     *      "companies": [
     *              {
     *                  "name": "AFIP",
     *                  "url_logo": "https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png",
     *                   "id": 1
     *              }
     *          ]
     *      }
     * }
     */
    public function get_companies()
    {                                                   // 1 week
        $companies = cache()->remember('companies', 60 * 60 * 24 * 7, function () {
            return Company::select('name', 'url_logo', 'id')->get();
        });

        return response()->success([
            'companies' => $companies,
        ], 'success');
    }

    /**
     * Index
     * 
     * Since all the user's credentials are returned on the endpoints for login, and the final register step, this method can be called to get all the credentials when there is an error on the local storage of the device.
     * 
     * <aside class="notice">You can check the README file to see the interface of Credentials.</aside>
     * 
     * @group Credential
     * 
     * @authenticated
     * 
     * @header Accept-Language es | en | jp
     * 
     * @response {
     *      "status": 200,
     *      "message": "Success!",
     *      "data": {
     *          "credentials": [
     *              {
     *                  "company_id": 1,
     *                  "company": {
     *                      "name": "AFIP",
     *                      "url_logo": "https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png",
     *                      "id": 1,
     *                  },
     *                  "email": {
     *                      "opening": "mr",
     *                      "char_count": 8,
     *                      "ending": "@gmail.com"
     *                  },
     *                  "password": {
     *                      "char_count": 16
     *                  }
     *              }
     *          ]
     *      }
     * }
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $credentials = Slot::with(
            'email',
            'password',
            'phone_number',
            'security_code',
            'security_question_answer',
            'username'
        )
            ->where('user_id', $user->id)
            ->get();

        $user_credentials = is_null($credentials) ? [] : $credentials;

        return response()->success(['credentials' => $user_credentials], 'success');
    }

    /**
     * Create
     * 
     * This is the method used to create a new credential.
     * 
     * <aside class="notice">This method will return the created Credential, you can see the Credential interface on the README file.</aside>
     * 
     * @group Credential
     * 
     * @authenticated
     * 
     * @header Accept-Language es | en | jp
     * 
     * @bodyParam company_name stribg required (min: 1, max: 190 char).
     * @bodyParam description string (min: 0, max: 500 char).
     * @bodyParam user_name string (min: 1, max: 190 char).
     * @bodyParam email string (min:5, max: 190 char).
     * @bodyParam password string (min: 5, max: 190 char).
     * @bodyParam username string (min:1, max: 190 char).
     * @bodyParam phone_number string (min: 8, max 190 char).
     * @bodyParam security_question string (min: 5, max: 190 char).
     * @bodyParam security_answer string Is required if security_question is not null (min: 5, max: 190 char).
     * @bodyParam unique_code string (min: 1, max: 190 char).
     * @bodyParam multiple_codes string[] Each string in the array must be different from the rest. Also each string must contain at least 1, and at max 25 characters.
     * @bodyParam crypto_codes string[] Each string in the array must be different from the rest. Also each string must contain at least 1, and at max 25 characters.
     * @bodyParam accessing_device string required The user agent of the navigator, or the unique ID of the device. (min: 1, max: 190 char).
     * @bodyParam accessing_plattform string required Must be one of these exact three options: "web", "desktop", "mobile".
     * 
     * @response {
     *      "status": 200,
     *      "message": "Success!",
     *      "data": {
     *          "credentials": {
     *                  "company_id": 1,
     *                  "company": {
     *                      "name": "AFIP",
     *                      "url_logo": "https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png",
     *                      "id": 1,
     *                  },
     *                  "email": {
     *                      "opening": "fa",
     *                      "char_count": 8,
     *                      "ending": "@email_company.com"
     *                  },
     *                  "password": {
     *                      "char_count": 16
     *                  }
     *              }
     *      }
     * }
     * 
     * @response status=401 scenario="validation failed" {
     *      "status": 401,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "password": "\"password\" must have at least 5 characters."
     *              }
     *          ],
     *          "request": {
     *              "password": "pass",
     *              "company_name": "AFIP",
     *              "email": "fake_email@email_company.com"
     *          }
     *      }
     * }
     */
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

        $rules = $this->validation_rules;
        $rules['company_name'][0] = 'required';

        $validation = Validator::make($data, $rules);

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

        $company = Company::where('name', 'LIKE', '%' . $data['company_name'] . '%')->first();

        $credential = Slot::create([
            'user_id' => $user->id,
            'company_name' => isset($data['company_name']) && !isset($data['company_id']) ? ucwords($data['company_name']) : null,
            'company_id' => !is_null($company) ? $company->id : null,
            'last_seen' => now()->format('Y-m-d H:i:s'),
            'recently_seen' => true,
            'accessing_device' => $data['accessing_device'],
            'accessing_platform' => $data['accessing_platform'],
            'user_name' => isset($data['user_name']) ? Crypt::encryptString(ucwords($data['user_name'])) : null,
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

        UpdateCredentialJob::dispatch($credential->id)->delay(now()->addDays(3));

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

    /**
     * Update 
     * 
     * This is the method used for updating a credential. If any field is absent, it will be updated to a null type, or deleted.
     * 
     * The company_name cannot be updated.
     * 
     * <aside class="notice">This method will return the updated Credential, you can see the Credential interface on the README file.</aside>
     * 
     * @group Credential
     * 
     * @authenticated
     * 
     * @header Accept-Language es | en | jp
     * 
     * @urlParam credential_id int required The id of the credential to update.
     * 
     * @bodyParam description string (min: 0, max: 500 char).
     * @bodyParam user_name string (min: 1, max: 190 char).
     * @bodyParam email string (min:5, max: 190 char).
     * @bodyParam password string (min: 5, max: 190 char).
     * @bodyParam username string (min:1, max: 190 char).
     * @bodyParam phone_number string (min: 8, max 190 char).
     * @bodyParam security_question string (min: 5, max: 190 char).
     * @bodyParam security_answer string Is required if security_question is not null (min: 5, max: 190 char).
     * @bodyParam unique_code string (min: 1, max: 190 char).
     * @bodyParam multiple_codes string[] Each string in the array must be different from the rest. Also each string must contain at least 1, and at max 25 characters.
     * @bodyParam crypto_codes string[] Each string in the array must be different from the rest. Also each string must contain at least 1, and at max 25 characters.
     * @bodyParam accessing_device string required The user agent of the navigator, or the unique ID of the device. (min: 1, max: 190 char).
     * @bodyParam accessing_plattform string required Must be one of these exact three options: "web", "desktop", "mobile".
     * 
     * @response {
     *      "status": 200,
     *      "message": "Success!",
     *      "data": {
     *          "credential": {
     *                  "id": 1,
     *                  "company_id": 1,
     *                  "company": {
     *                      "name": "AFIP",
     *                      "url_logo": "https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png",
     *                      "id": 1,
     *                  },
     *                  "email": {
     *                      "opening": "fa",
     *                      "char_count": 8,
     *                      "ending": "@email_company.com"
     *                  },
     *                  "password": {
     *                      "char_count": 16
     *                  }
     *              }
     *      }
     * }
     * 
     * @response status=404 scenario="credential with id = credential-id was not found" {
     *      "status": 404,
     *      "errors": [
     *          {
     *              "message": "The credential was not found.",
     *          }
     *      ],
     *      "message": "The credential was not found.",
     * }
     * 
     * @response status=401 scenario="validation failed" {
     *      "status": 401,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "email": "The field "email" must must be a valid email."
     *              }
     *          ],
     *          "request": {
     *              "password": "pass",
     *              "company_name": "AFIP",
     *              "email": "fake_email.com",
     *              "id:" 1,
     *          }
     *      }
     * }
     */
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

        $credential = Slot::where('user_id', $user->id)->where('id', $credential_id)->firstOrFail();

        $credential->user_name = isset($data['user_name']) ? Crypt::encryptString($data['user_name']) : null;
        $credential->char_count = isset($data['user_name']) ? strlen($data['user_name']) : null;
        $credential->description = isset($data['description']) ? $data['description'] : '';

        $credential->save();

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
            (new CredentialService())->username_crud('delete', $credential->id);
        }

        (new CredentialService())->security_code_crud('update or delete', $credential->id, $data);

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

    /**
     * Delete
     * 
     * This method is used to delete a credential.
     * 
     * @group Credential
     * 
     * @authenticated
     * 
     * @header Accept-Language es | en | jp
     * 
     * @urlParam credential_id int required The id of the credential to delete.
     * 
     * @response {
     *      "status": 200,
     *      "message": "Succes!",
     *      "data": {}
     * }
     * 
     * @response status=404 scenario="credential with id = credential-id was not found" {
     *      "status": 404,
     *      "errors": [
     *          {
     *              "message": "The credential was not found.",
     *          }
     *      ],
     *      "message": "The credential was not found.",
     * }
     */
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

    /**
     * Find
     * 
     * This method can be called when the frontend app has failed to find a credential on the local storage of the device.
     * 
     * <aside class="notice">This method will return the Credential that was found, you can see the Credential interface on the README file.</aside>
     * 
     * @group Credential
     * 
     * @authenticated
     * 
     * @header Accept-Language es | en | jp
     * 
     * @urlParam credential_id int required The id of the credential to delete.
     * 
     * @response {
     *      "status": 200,
     *      "message": "Success!",
     *      "data": {
     *          "credential": {
     *                  "company_id": 1,
     *                  "company": {
     *                      "name": "AFIP",
     *                      "url_logo": "https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png",
     *                      "id": 1,
     *                  },
     *                  "email": {
     *                      "opening": "mr",
     *                      "char_count": 8,
     *                      "ending": "@gmail.com"
     *                  },
     *                  "password": {
     *                      "char_count": 16
     *                  },
     *              },
     *      },
     * }
     * 
     * @response status=404 scenario="credential with id = credential-id was not found" {
     *      "status": 404,
     *      "errors": [
     *          {
     *              "message": "The credential was not found.",
     *          }
     *      ],
     *      "message": "The credential was not found.",
     * }
     */
    public function find(Request $request, $credential_id)
    {
        $user = $request->user();

        $credential = Slot::with(
            'email',
            'password',
            'phone_number',
            'security_code',
            'security_question_answer',
            'username'
        )
            ->where('user_id', $user->id)
            ->where('slot_id', $credential_id)
            ->firstOrFail();

        return response()->success(['credential' => $credential], 'success');
    }

    /**
     * Get Recently Seen
     * 
     * This method will return some info of the credentials that were accessed within the last 3 days. 
     * 
     * @group Credential
     * 
     * @authenticated
     * 
     * @header Accept-Language es | en | jp
     * 
     * @response {
     *      "message": "Success!",
     *      "status": 200,
     *      "data": {
     *          "recently_seen": [
     *              {
     *                  "company_name": "AFIP",
     *                  "last_seen": "2021-12-23 10:05:31",
     *                  "id": 1,
     *                  "accessing_device": "Windows NT 6.1; Win64; x64; rv:47.0",
     *                  "accessing_plattform": "web",
     *                  "created_at": "2021-12-23 10:05:31",
     *                  "updated_at": "2021-12-23 10:05:31",
     *              },
     *          ]
     *      }
     * }
     * 
     * @response status=200 scenario="no credential was recently accessed" {
     *      "message": "Success!",
     *      "status": 200,
     *      "data": {
     *          "recently_seen": []
     *      }
     * }
     */
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
        )
            ->where('recently_seen', true)
            ->orderBy('last_seen', 'DESC')
            ->get();

        return response()->success(['recently_seen' => $credentials], 'success');
    }
}
