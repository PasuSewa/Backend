<?php

namespace Tests\Feature;

use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    // There's no need to encrypt anything in this case
    protected $user = [
        'name' => 'user name',
        'email' => 'main@email.com',
        'recovery_email' => 'recovery@email.com',
        'phone_number' => '+540113585031',
        'two_factor_secret' => "2YXIJ4AE6RP4HTW3",
        'anti_fishing_secret' => 'secret',
        'preferred_lang' => 'en',
        'invitation_code' => '4LGDR0COFF',
        'recovery_code' => 'AJF682JK9S'
    ];

    /** @test */
    public function user_can_retrieve_feedback()
    {
        $user = User::withoutEvents(function () {
            return User::create($this->user);
        });

        $user->assignRole('premium');

        $token = JWTAuth::fromUser($user);

        $suggestion_data = [
            'userName' => $user->name,
            'body' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
            'email' => $user->email,
            'type' => true,
        ];

        $rating_data = [
            'userName' => $user->name,
            'body' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
            'rating' => 7,
            'email' => $user->email,
            'type' => false,
        ];

        $response_suggestion = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('POST', '/api/feedback/create', $suggestion_data);

        $response_suggestion->assertOk();

        $this->assertDatabaseHas('feedback', [
            'user_name' => $user->name,
            'body' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
            'rating' => null,
            'type' => 1,
        ]);


        $response_rating = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('POST', '/api/feedback/create', $rating_data);

        $response_rating->assertOk();

        $this->assertDatabaseHas('feedback', [
            'user_name' => $user->name,
            'body' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
            'rating' => 7,
            'type' => 0,
        ]);
    }

    /** @test */
    public function public_feedback_is_visible()
    {
        $user = User::withoutEvents(function () {
            return User::create($this->user);
        });

        $suggestion_data = [
            'user_name' => $user->name,
            'body' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
            'rating' => null,
            'type' => true,
            'is_public' => true
        ];

        $rating_data = [
            'user_name' => $user->name,
            'body' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
            'rating' => 7,
            'type' => false,
            'is_public' => true
        ];

        Feedback::create($suggestion_data);
        Feedback::create($rating_data);

        $response = $this->json('GET', '/api/feedback/index');

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'feedback' => [
                    'suggestions',
                    'ratings'
                ]
            ],
            'message'
        ]);
    }

    /** @test */
    public function user_cant_retrieve_feedback()
    {
        $user = User::withoutEvents(function () {
            return User::create($this->user);
        });

        $user->assignRole('free');

        $token = JWTAuth::fromUser($user);

        // No need for data, because the user shouldn't reach the validation in the first place
        $json_data = [];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('POST', '/api/feedback/create', $json_data);

        $response->assertStatus(403);
    }
}
