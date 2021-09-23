<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\UserWasUpdated;
use Illuminate\Support\Facades\Crypt;

use Validator;

class UserController extends Controller
{
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
}
