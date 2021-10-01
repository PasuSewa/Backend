<?php

namespace Tests\Feature;

use App\Jobs\UpdateCredentialJob;

use App\Models\User;
use App\Models\Slot;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CredentialTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    /** @test */
    public function user_can_create_credentiasl()
    {
        Bus::fake();

        $user = User::find(1);

        $token = JWTAuth::fromUser($user);

        //*********************************************************************** * user can create a full credential (with all preperties)
        $json_data = [
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
            'company_name' => 'testing name 1',
            'user_name' => 'user name 1',
            'description' => 'description 1',
            'email' => 'email_1@gmail.com',
            'password' => 'password1234', // this is my password, please don't use it LOL
            'username' => 'Professor Salvatore',
            'phone_number' => '+54 011 1234 - 5678',
            'security_question' => 'question?',
            'security_answer' => 'answer!',
            'unique_security_code' => 'UNIQUE-SECURITY-CODE',
            'multiple_security_code' => [
                'ABC1234',
                'ABC1235',
                'ABC1236',
                'ABC1237',
                'ABC1238',
            ],
            'crypto_currency_access_codes' => [
                'phrase 1',
                'phrase 2',
                'phrase 3',
                'phrase 4',
                'phrase 5',
                'phrase 6',
                'phrase 7',
            ],
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
        ];

        $response_1 = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('POST', '/api/credential/create', $json_data);
        $response_1->assertOk();

        $this->assertDatabaseHas('slots', [
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
            'company_name' => 'testing name 1',
            'description' => 'description 1',
        ]);

        $this->assertDatabaseHas('emails', [
            'slot_id' => 1,
            'opening' => substr($json_data['email'], 0, 2),
            'ending' => explode('@', $json_data['email'], 2)[1]
        ]);

        $this->assertDatabaseHas('passwords', [
            'slot_id' => 1,
            'char_count' => strlen($json_data['password']),
        ]);

        $this->assertDatabaseHas('phone_numbers', [
            'slot_id' => 1,
            'opening' => substr($json_data['phone_number'], 0, 3),
            'char_count' => strlen($json_data['phone_number']) - 5,
            'ending' => substr($json_data['phone_number'], -2)
        ]);

        $this->assertDatabaseHas('security_codes', [
            'slot_id' => 1,
            'multiple_codes_length' => count($json_data['multiple_security_code']),
            'crypto_codes_length' => count($json_data['crypto_currency_access_codes']),
        ]);

        $this->assertDatabaseHas('security_questions_answers', ['slot_id' => 1]);

        $this->assertDatabaseHas('usernames', [
            'slot_id' => 1,
            'char_count' => strlen($json_data['username']),
        ]);

        Bus::assertDispatched(UpdateCredentialJob::class);

        //*********************************************************************** * user can create a credential without anything, just name and description
        $json_data = [
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
            'company_name' => 'testing name 3',
            'description' => 'testing description 3',
        ];

        $response_3 = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('POST', '/api/credential/create', $json_data);
        $response_3->assertOk();
        $this->assertDatabaseHas('slots', $json_data);

        Bus::assertDispatched(UpdateCredentialJob::class);
    }

    /** @test */
    public function get_recent_access()
    {
        Slot::create([
            'user_id' => 1,
            'last_seen' => now()->format('Y-m-d H:i:s'),
            'recently_seen' => true,
            'accessing_device' => 'mi pc for testing',
            'accessing_platform' => 'web',
            'company_name' => 'testing recently seen'
        ]);

        $user = User::find(1);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('GET', '/api/credential/get-recently-seen');
        $response->assertOk();

        $response->assertJsonStructure([
            'message',
            'status',
            'data' => [
                'recently_seen'
            ]
        ]);
    }
}
