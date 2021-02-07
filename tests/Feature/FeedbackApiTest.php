<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FeedbackApiTest extends TestCase
{
    /** @test */
    public function user_can_send_suggestions()
    {
        $response = $this->post();

        $response->assertJson([
            'message' => "Thank you for your suggestion, we will take it into count."
        ]);
    }

    /** @test */
    public function user_can_send_ratings()
    {

    }

    public function users_can_see_suggestions_and_ratings()
    {
        $response = $this->get('/api/feedback/index');

        $response->assertOk();

        $suggestion = Feedback::where('feedback_type', 'suggestion')->first();

        $rating = Feedback::where('feedback_type', 'rating')->first();

        // aca solo va a haber una de cada una, que son las creadas por las pruebas anteriores

        $response->assertJson([
            'feedback' => [
                'suggestions' => [
                    [
                        'user_name' => $suggestion->user_name,
                        'body' => $suggestion->body,
                        'feedback_type' => $suggestion->feedback_type
                    ],
                ],
                'ratings' => [
                    [
                        'user_name' => $rating->user_name,
                        'body' => $rating->body,
                        'rating' => $rating->rating,
                        'feedback_type' => $rating->feedback_type
                    ],
                ],
            ],
        ]);
    }

    public function feedback_api_test_is_working_correctly()
    {

    }
}
