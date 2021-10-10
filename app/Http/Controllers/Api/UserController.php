<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\UserWasUpdated;
use Illuminate\Support\Facades\Crypt;

use Validator;

class UserController extends Controller
{
    /**
     * Update
     * 
     * This method is used to edit the user's credentials for accessing their account in PasuNashi.
     * 
     * <aside class="notice">All of the parameters are required, even if nothing changed.</aside>
     * 
     * @group User
     * @authenticated
     * @header Accept-Language es | en | jp
     * 
     * @bodyParam name string required (min: 3, max: 190 char).
     * @bodyParam phone_number string required (min: 8, max: 16 char).
     * @bodyParam email string required (max: 190 char).
     * @bodyParam recovery_email required (max: 190 char).
     * @bodyParam anti_fishing_secret required (min: 5, max: 190 char).
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
     *                  "name": ""name" must have at least 3 characters."
     *              }
     *          ],
     *          "request": {
     *              "neame": "",
     *              "phone_number": "+1 555-1234-1234",
     *              "email": "fake@email.com",
     *              "recovery_email": "fake_email@email_company.com"
     *              "anti_fishing_secret": "secret",
     *      }
     *  }
     */
    public function update(Request $request)
    {
        $data = $request->only('name', 'phone_number', 'email', 'recovery_email', 'anti_fishing_secret');

        $user = $request->user();

        $email_rules = ['required', 'email', 'max:190', 'unique:users,email,' . $user->id, 'unique:users,recovery_email,' . $user->id];

        $validation = Validator::make($data, [
            'name' => ['required', 'string', 'min:3', 'max:190'],
            'phone_number' => ['required', 'string', 'min:8', 'max:16'],
            'email' => $email_rules,
            'recovery_email' => $email_rules,
            'anti_fishing_secret' => ['required', 'string', 'min:5', 'max:190'],
        ]);

        if ($validation->fails()) {

            $data = [
                'request' => $request->all(),
                'errors' => $validation->errors(),
            ];

            return response()->error($data, 'api_messages.error.validation', 401);
        }

        $user->name = $data['name'];
        $user->phone_number = Crypt::encryptString($data['phone_number']);
        $user->email = $data['email'];
        $user->recovery_email = $data['recovery_email'];
        $user->anti_fishing_secret = Crypt::encryptString($data['anti_fishing_secret']);

        $user->save();

        $user->notify(new UserWasUpdated($data['anti_fishing_secret'], $user->preferred_lang));

        return response()->success([], 'auth.user_updated_successfully');
    }

    /**
     * Stop Premium
     * 
     * This method is for the times when some user may want to stop paying for the "premium" role, and go back to their previous role.
     * 
     * <aside class="notice">This method can only be used if the user making the request already has the "premium" role.</aside>
     * 
     * @group User
     * @authenticated
     * @header Accept-Language es | en | jp
     * 
     * @response {
     *      "status": 200,
     *      "message": "Succes!",
     *      "data": {}
     * }
     */
    public function stop_premium(Request $request)
    {
        $user = $request->user();

        $user->removeRole('premium');

        return response()->success([], 'user.stopped_premium');
    }

    /**
     * Update Preferred Lang
     * 
     * It may be obvous at this point, but this is the method used for updating the user's preferred language.
     * 
     * @group User
     * @authenticated
     * @header Accept-Language es | en | jp
     * 
     * @bodyParam preferredLang string required One of these (exact) three options: "en", "es", "jp"
     * 
     * @response {
     *      "status": 200,
     *      "message": "Succes!",
     *      "data": {}
     * }
     */
    public function update_preferred_lang(Request $request)
    {
        $data = $request->only('preferredLang');

        $validation = Validator::make($data, [
            'preferredLang' => ['required', 'string', 'min:2', 'max:2', 'in:en,es,jp'],
        ]);

        if ($validation->fails()) {
            $data = [
                'request' => $request->all(),
                'errors' => $validation->errors(),
            ];

            return response()->error($data, 'api_messages.error.validation', 401);
        }

        $user = $request->user();
        $user->preferred_lang = $data['preferredLang'];
        $user->save();

        return response()->success([], 'user_updated_preferred_lang');
    }
}
