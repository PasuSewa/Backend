<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Response;

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
        Paginator::useBootstrap();

        Response::macro('succes', function ($data, $message) {
            return response()->json([
                'status' => 200,
                'message' => __('api_messages.success.' . $message),
                'data' => $data,
            ], 200);
        });

        Response::macro('user_was_authenticated', function ($data, $message) {
            return response()->json([
                'status' => 200,
                'message' => __('api_responses.success.auth.' . $message),
                'data' => [
                    'user_data' => [
                        'id' => $data['user']->id,
                        'name' => $data['user']->name,
                        'email' => $data['user']->email,
                        'recovery_email' => $data['user']->recovery_email,
                        'slots_available' => $data['user']->slots_available,
                        'invitation_code' => $data['user']->invitation_code,
                        'role' => $data['user']->getRoleNames()[0], // users only have 1 role
                    ],
                    'user_credentials' => $data['credentials']
                ],
            ], 200);
        });

        Response::macro('user_was_authorized', function ($message) {
            return response()->json([
                'status' => 200,
                'message' => __('api_responses.success.auth.' . $message),
                'data' => [
                    'is_authorized' => true
                ],
            ], 200);
        });

        Response::macro('error', function ($data, $message, $status_code) {
            return response()->json([
                'status' => $status_code,
                'message' => __($message),
                'errors' => $data['errors'],
                'request' => isset($data['request']) ? $data['request'] : null
            ], $status_code);
        });
    }
}
