<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Response;

use Illuminate\Pagination\Paginator;

use App\Models\Slot;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        URL::forceScheme('https');

        Paginator::useBootstrap();

        Response::macro('success', function ($data, $message) {
            return response()->json([
                'status' => 200,
                'message' => __('api_messages.success.' . $message),
                'data' => $data,
            ], 200);
        });

        Response::macro(
            'user_was_authenticated',
            function (
                $data,
                $message,
                $with_token = false,
                $with_credentials = false,
                $mobile = false
            ) {

                if ($with_credentials) {
                    $credentials = Slot::with(
                        'email',
                        'password',
                        'phone_number',
                        'security_code',
                        'security_question_answer',
                        'username',
                        'company'
                    )
                        ->where('user_id', $data['user']->id)
                        ->get();

                    $user_credentials = is_null($credentials) ? [] : $credentials;
                } else {
                    $user_credentials = [];
                }

                return response()->json([
                    'status' => 200,
                    'message' => __('api_messages.success.auth.' . $message),
                    'data' => [
                        'user_data' => [
                            'id' => $data['user']->id,
                            'name' => $data['user']->name,
                            'email' => $data['user']->email,
                            'recovery_email' => $data['user']->recovery_email,
                            'slots_available' => $data['user']->slots_available,
                            'invitation_code' => $data['user']->invitation_code,
                            'role' => $data['user']->getRoleNames()[0], // users only have 1 role
                            'preferred_lang' => $data['user']->preferred_lang
                        ],
                        'user_credentials' => $user_credentials,
                        'token' => $with_token
                            ?
                            auth('api')
                            ->setTTL($mobile ? null : 120) // 120 mins if plattform is different from mobile
                            ->tokenById($data['user']->id)
                            :
                            null,
                    ],
                ], 200);
            }
        );

        Response::macro('error', function ($data, $message, $status_code) {
            return response()->json([
                'status' => $status_code,
                'message' => __($message),
                'data' => [
                    'errors' => $data['errors']
                ],
                'request' => isset($data['request']) ? $data['request'] : null,
            ], $status_code);
        });
    }
}
