<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Crypt;

use Database\Seeders\PermissionSeeder;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register()
    {
        $this->seed();

        // step 1
        $json_data = [
            'name' => 'testing_name',
            'phoneNumber' => 'number',
            'mainEmail' => 'main_email@gmail.com',
            'recoveryEmail' => 'recovery_email@gmail.com',
            'secretAntiFishing' => 'secret',
            'secretAntiFishing_confirmation' => 'secret',
            'invitationCode' => '',
        ];

        $response_step_1 = $this->json('POST', '/api/auth/register/step-1', $json_data);

        $response_step_1->assertOk();

        $this->assertDatabaseHas('users', ['email' => $json_data['mainEmail']]);

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

        $response_step_2->assertJsonStructure([
            'data' => [
                'token'
            ]
        ]);

        $decoded_response_step_2 = $response_step_2->decodeResponseJson();

        // step 3
        $response_step_3 = $this->withHeader('Authorization', 'Bearer ' . $decoded_response_step_2['data']['token'])->json('POST', '/api/auth/register/step-3', [
            'twoFactorCode' => 000001
        ]);

        $response_step_3->assertOk();
    }

    /** @test */
    public function user_can_spend_invitation_code()
    {
        $this->seed();

        $json_data = [
            'name' => 'testing_name',
            'phoneNumber' => 'number',
            'mainEmail' => 'main_email@gmail.com',
            'recoveryEmail' => 'recovery_email@gmail.com',
            'secretAntiFishing' => 'secret',
            'secretAntiFishing_confirmation' => 'secret',
            'invitationCode' => '4LGDR0COFF',
        ];

        $response_step_1 = $this->json('POST', '/api/auth/register/step-1', $json_data);

        $response_step_1->assertOk();

        $this->assertDatabaseHas('users', ['email' => $json_data['mainEmail'], 'slots_available' => 10]);
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
