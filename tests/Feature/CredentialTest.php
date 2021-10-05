<?php

namespace Tests\Feature;

use App\Jobs\UpdateCredentialJob;

use App\Models\Email;
use App\Models\Password;
use App\Models\PhoneNumber;
use App\Models\QuestionAnswer;
use App\Models\SecurityCode;
use App\Models\User;
use App\Models\Slot;
use App\Models\Username;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'unique_code' => 'UNIQUE-SECURITY-CODE',
            'multiple_codes' => [
                'ABC1234',
                'ABC1235',
                'ABC1236',
                'ABC1237',
                'ABC1238',
            ],
            'crypto_codes' => [
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
            'ending' => '@' . explode('@', $json_data['email'], 2)[1]
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
            'multiple_codes_length' => count($json_data['multiple_codes']),
            'crypto_codes_length' => count($json_data['crypto_codes']),
            'unique_code_length' => strlen($json_data['unique_code']),
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
    public function delete_credential()
    {
        $credential = Slot::create([
            'user_id' => 1,
            'last_seen' => now()->format('Y-m-d H:i:s'),
            'recently_seen' => true,
            'accessing_device' => 'mi pc for testing',
            'accessing_platform' => 'web',
            'company_name' => 'testing recently seen'
        ]);

        $user = User::find(1);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('GET', '/api/credential/delete/' . $credential->id);
        $response->assertOk();

        $response->assertJsonStructure([
            'message',
            'status',
            'data'
        ]);

        $this->assertDatabaseMissing('slots', ['id' => $credential->id]);
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

    /** @test */
    public function get_companies()
    {
        $response = $this->json('GET', '/api/companies/index');
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'companies'
            ]
        ]);
    }

    /** @test */
    public function it_updates_all_properties_of_a_credential()
    {
        $old_json_data = [
            'company_name' => 'testing',
            'description' => 'old description',
            'user_name' => 'old name',
            'email' => 'email@old.com',
            'password' => 'old password',
            'username' => 'old username',
            'phone_number' => 'old phone number',
            'security_question' => 'old question',
            'security_answer' => 'old answer',
            'unique_code' => 'old unique code',
            'multiple_codes' => [
                'old code 1',
                'old code 2',
                'old code 3',
            ],
            'crypto_codes' => [
                'old code 1',
                'old code 2',
                'old code 3',
                'old code 4',
                'old code 5',
                'old code 6',
                'old code 7',
            ],
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
        ];

        $new_json_data = [
            'description' => 'new description',
            'user_name' => 'new name',
            'email' => 'email@new.com',
            'password' => 'new password',
            'username' => 'new username',
            'phone_number' => 'new phone number',
            'security_question' => 'new question',
            'security_answer' => 'new answer',
            'unique_code' => 'new unique code',
            'multiple_codes' => [
                'new code 1',
                'new code 2',
                'new code 3',
            ],
            'crypto_codes' => [
                'new code 1',
                'new code 2',
                'new code 3',
                'new code 4',
                'new code 5',
                'new code 6',
                'new code 7',
            ],
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
        ];

        $user = User::find(1);
        $token = JWTAuth::fromUser($user);

        $old_response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('POST', '/api/credential/create', $old_json_data);
        $old_response->assertOk();

        $this->assertDatabaseHas('slots', [
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
            'company_name' => 'testing',
            'description' => 'old description',
        ]);

        $credential_id = $old_response->json()['data']['credential']['id'];

        $new_response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('PUT', '/api/credential/update/' . $credential_id, $new_json_data);
        $new_response->assertOk();

        $email = Email::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($email->email), $new_json_data['email']);

        $password = Password::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($password->password), $new_json_data['password']);

        $phone_number = PhoneNumber::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($phone_number->phone_number), $new_json_data['phone_number']);

        $username = Username::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($username->username), $new_json_data['username']);

        $code = SecurityCode::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($code->unique_code), $new_json_data['unique_code']);
        $this->assertEquals(Crypt::decryptString($code->multiple_codes), implode('<@>', $new_json_data['multiple_codes']));
        $this->assertEquals(Crypt::decryptString($code->crypto_codes), implode('<@>', $new_json_data['crypto_codes']));

        $question_answer = QuestionAnswer::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($question_answer->security_question), $new_json_data['security_question']);
        $this->assertEquals(Crypt::decryptString($question_answer->security_answer), $new_json_data['security_answer']);
    }

    /** @test */
    public function it_deletes_unused_properties()
    {
        $old_json_data = [
            'company_name' => 'testing',
            'description' => 'old description',
            'user_name' => 'old name',
            'email' => 'email@old.com',
            'password' => 'old password',
            'username' => 'old username',
            'phone_number' => 'old phone number',
            'security_question' => 'old question',
            'security_answer' => 'old answer',
            'unique_code' => 'old unique code',
            'multiple_codes' => [
                'old code 1',
                'old code 2',
                'old code 3',
            ],
            'crypto_codes' => [
                'old code 1',
                'old code 2',
                'old code 3',
                'old code 4',
                'old code 5',
                'old code 6',
                'old code 7',
            ],
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
        ];

        $new_json_data = [
            'company_name' => 'testing',
            'description' => 'new description',
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
        ];

        $user = User::find(1);
        $token = JWTAuth::fromUser($user);

        $old_response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('POST', '/api/credential/create', $old_json_data);
        $old_response->assertOk();

        $this->assertDatabaseHas('slots', [
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
            'company_name' => 'testing',
            'description' => 'old description',
        ]);

        $credential_id = $old_response->json()['data']['credential']['id'];

        $new_response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('PUT', '/api/credential/update/' . $credential_id, $new_json_data);
        $new_response->assertOk();

        $this->assertDatabaseMissing('emails', ['slot_id' => $credential_id]);
        $this->assertDatabaseMissing('passwords', ['slot_id' => $credential_id]);
        $this->assertDatabaseMissing('phone_numbers', ['slot_id' => $credential_id]);
        $this->assertDatabaseMissing('security_codes', ['slot_id' => $credential_id]);
        $this->assertDatabaseMissing('security_questions_answers', ['slot_id' => $credential_id]);
        $this->assertDatabaseMissing('usernames', ['slot_id' => $credential_id]);
    }

    /** @test */
    public function it_creates_new_properties()
    {
        $old_json_data = [
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
            'company_name' => 'testing',
            'description' => 'old description',
        ];

        $new_json_data = [
            'description' => 'new description',
            'user_name' => 'new name',
            'email' => 'email@new.com',
            'password' => 'new password',
            'username' => 'new username',
            'phone_number' => 'new phone number',
            'security_question' => 'new question',
            'security_answer' => 'new answer',
            'unique_code' => 'new unique code',
            'multiple_codes' => [
                'new code 1',
                'new code 2',
                'new code 3',
            ],
            'crypto_codes' => [
                'new code 1',
                'new code 2',
                'new code 3',
                'new code 4',
                'new code 5',
                'new code 6',
                'new code 7',
            ],
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
        ];

        $user = User::find(1);
        $token = JWTAuth::fromUser($user);

        $old_response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('POST', '/api/credential/create', $old_json_data);
        $old_response->assertOk();

        $this->assertDatabaseHas('slots', [
            'accessing_device' => 'my pc for testing',
            'accessing_platform' => 'web',
            'company_name' => 'testing',
            'description' => 'old description',
        ]);

        $credential_id = $old_response->json()['data']['credential']['id'];

        $new_response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('PUT', '/api/credential/update/' . $credential_id, $new_json_data);
        $new_response->assertOk();

        $email = Email::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($email->email), $new_json_data['email']);

        $password = Password::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($password->password), $new_json_data['password']);

        $phone_number = PhoneNumber::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($phone_number->phone_number), $new_json_data['phone_number']);

        $username = Username::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($username->username), $new_json_data['username']);

        $code = SecurityCode::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($code->unique_code), $new_json_data['unique_code']);
        $this->assertEquals(Crypt::decryptString($code->multiple_codes), implode('<@>', $new_json_data['multiple_codes']));
        $this->assertEquals(Crypt::decryptString($code->crypto_codes), implode('<@>', $new_json_data['crypto_codes']));

        $question_answer = QuestionAnswer::where('slot_id', $credential_id)->firstOrFail();
        $this->assertEquals(Crypt::decryptString($question_answer->security_question), $new_json_data['security_question']);
        $this->assertEquals(Crypt::decryptString($question_answer->security_answer), $new_json_data['security_answer']);
    }
}
