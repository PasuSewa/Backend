<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Crypt;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // use DatabaseMigrations;

    public function user_can_register()
    {
        $this->seed();

        // step 1
        $json_data = [
            'name' => 'testing_name',
            'phoneNumber' => 'number',
            'mainEmail' => 'main_email@gmail.com',
            'secondaryEmail' => 'recovery_email@gmail.com',
            'secretAntiFishing' => 'secret',
            'secretAntiFishing_confirmation' => 'secret',
            'invitationCode' => '',
        ];

        $response_step_1 = $this->json('POST', '/api/auth/register/step-1', $json_data);

        $response_step_1->assertOk();

        $this->seeInDatabase('users', ['email' => $json_data['mainEmail']]);

        // step 2
        $db_user = User::where('email', $json_data['mainEmail'])->select('two_factor_code_email', 'two_factor_code_recovery')->first();

        $main_code = $db_user->two_factor_code_email;
        $second_code = $db_user->two_factor_code_recovery;

        $response_step_2 = $this->json('POST', '/api/auth/register/step-2', [
            'mainEmailCode' => Crypt::decryptString(($main_code)),
            'recoveryEmailCode' => Crypt::decryptString($second_code),
            'mainEmail' => $json_data['mainEmail'],
        ]);

        $response_step_2->assertOk();

        $response_2->assertJsonStructure([
            'data' => [
                'token'
            ]
        ]);

        $decoded_response_2 = $response_2->decodeResponseJson();

        // step 3
        $response_3 = $this->withHeader('Authorization', 'Bearer ' . $decoded_response_2['data']['token'])->json('POST', '/api/auth/register/step-3', [
            'twoFactorCode' => 000001
        ]);

        $response_3->assertOk();
    }

    public function user_can_spend_invitation_code()
    {
    }

    public function user_can_refresh_2fa_secret()
    {
    }

    public function app_sends_emails()
    {
    }

    public function login_by_g2fa()
    {
    }

    public function login_by_email_code()
    {
    }

    public function login_by_security_code()
    {
    }
}
