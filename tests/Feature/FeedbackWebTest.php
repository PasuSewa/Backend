<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Feedback;
use App\Models\User;

class FeedbackWebTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function suggestion_can_be_published()
    {
        // $this->withoutExceptionHandling(); para que muestre los errores crudos

        $suggestion = Feedback::factory()->count(1)->create([
            'feedback_type' => 'suggestion',
        ]);

        $admin = User::find(1);

        $response = $this->actingAs($admin, 'web')->get('/suggestion/publish/' . $suggestion[0]->id);

        $response->assertStatus(302 , $response->getStatusCode()); // tiene que ser 302 en este caso, porque estamos siendo redirigidos

        $response->assertRedirect('/dashboard');

        // $response->assertOk(); // esto es para 200

        $this->assertCount(1, Feedback::where('feedback_type', 'suggestion')->get());

        // $this->assertEquals($algo->propiedad, 'algo que yo le de'); esto es para que verifique q lo q creó sea igual a lo que obtiene de la base de datos

        // $response->assertViewIs('dashboard');
        // $response->assertViewHas('suggestion', Feedback::where('feedback_type', 'suggestion')->first());
    }

    /** @test */
    public function suggestion_can_be_discarded()
    {
        $admin = User::find(1);

        $suggestion = Feedback::where('feedback_type', 'suggestion')->first(); // solo va a haber 1, creada por la prueba anterior

        $response = $this->actingAs($admin, 'web')->get('/suggestion/discard/' . $suggestion->id);

        $response->assertStatus(302 , $response->getStatusCode());
        
        $response->assertRedirect('/dashboard');

        $this->assertCount(0, Feedback::where('feedback_type', 'suggestion')->get());
    }
    
    /** @test */
    public function rating_can_be_published()
    {
        $rating = Feedback::factory()->count(1)->create([
            'feedback_type' => 'rating',
        ]);

        $admin = User::find(1);

        $response = $this->actingAs($admin, 'web')->get('/rating/publish/' . $rating[0]->id);

        $response->assertStatus(302 , $response->getStatusCode());

        $response->assertRedirect('/dashboard');

        $this->assertCount(1, Feedback::where('feedback_type', 'rating')->get());
    }
    
    /** @test */
    public function rating_can_be_discarded()
    {
        $admin = User::find(1);

        $rating = Feedback::where('feedback_type', 'rating')->first();

        $response = $this->actingAs($admin, 'web')->get('/rating/discard/' . $rating->id);

        $response->assertStatus(302 , $response->getStatusCode()); 

        $response->assertRedirect('/dashboard');

        $this->assertCount(0, Feedback::where('feedback_type', 'rating')->get());
    }

    /** @test */
    public function feedback_web_test_is_working_correctly()
    {
        $feedback = Feedback::all();

        $this->assertCount(0, $feedback);
    }
}
