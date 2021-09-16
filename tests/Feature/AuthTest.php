<?php

namespace Tests\Feature;

use App\Models\User;

use App\Notifications\EmailTwoFactorAuth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use Illuminate\Support\Facades\Crypt;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Notification;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    /** @test */
    public function user_can_register()
    {
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

    /** @test */
    public function user_can_refresh_2fa_secret()
    {
        $user = User::find(1);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('GET', '/api/auth/refresh-2fa-secret');

        $response->assertOk();

        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'secret'
            ],
        ]);
    }

    /** @test */
    public function app_sends_emails()
    {
        Notification::fake();

        $user = User::find(1);

        $response = $this->json('POST', '/api/send-code-by-email', [
            'email' => $user->email,
            'isSecondary' => false,
        ]);

        $response->assertOk();

        Notification::assertSentTo(
            [$user],
            EmailTwoFactorAuth::class
        );
    }

    public function login_by_g2fa()
    {
        $response = $this->json('POST', '/auth/login/two-factor-code', ['email' => 'mr.corvy@gmail.com', 'code' => 123456]);

        $response->assertOk();

        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user_data',
                'user_credentials',
                'token'
            ],
        ]);
    }

    /** @test */
    public function login_by_email_code()
    {
        Notification::fake();

        $response_1 = $this->json('POST', '/api/send-code-by-email', [
            'email' => 'gonzalosalvadorcorvalan@gmail.com',
            'isSecondary' => true,
        ]);

        $response_1->assertOk();

        $user = User::find(1);

        $code = Crypt::decryptString($user->two_factor_code_recovery);

        $response_2 = $this->json('POST', '/api/auth/login/email-code', [
            'mainEmail' => 'mr.corvy@gmail.com',
            'recoveryEmail' => 'gonzalosalvadorcorvalan@gmail.com',
            'code' => $code
        ]);

        $response_2->assertOk();

        $response_2->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user_data',
                'user_credentials',
                'token'
            ],
        ]);
    }

    public function login_by_security_code()
    {
    }

    public function logout()
    {
    }
}
