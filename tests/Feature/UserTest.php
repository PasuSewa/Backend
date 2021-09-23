<?php

namespace Tests\Feature;

use App\Models\User;

use App\Notifications\UserWasUpdated;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    /** @test */
    public function users_can_update_their_access_data()
    {
        $json_data = [
            'name' => 'new name',
            'phone_number' => '+155512398837564',
            'email' => 'new@email.com',
            'recovery_email' => 'new_recovery@email.com',
            'anti_fishing_secret' => 'new secret'
        ];

        Notification::fake();

        $old_user = User::find(1);

        $token = JWTAuth::fromUser($old_user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('PUT', '/api/user/update', $json_data);

        $response->assertOk();

        Notification::assertSentTo(
            [$old_user],
            UserWasUpdated::class
        );

        $this->assertDatabaseHas('users', [
            'name' => $json_data['name'],
            'email' => $json_data['email'],
            'recovery_email' => $json_data['recovery_email'],
        ]);

        $new_user = User::find(1);

        $this->assertTrue($json_data['phone_number'] === Crypt::decryptString($new_user->phone_number));
        $this->assertTrue($json_data['anti_fishing_secret'] === Crypt::decryptString($new_user->anti_fishing_secret));
    }
}
