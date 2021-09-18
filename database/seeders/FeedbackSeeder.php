<?php

namespace Database\Seeders;

use App\Models\Feedback;
use Illuminate\Database\Seeder;
use Faker\Generator;
use Illuminate\Container\Container;

class FeedbackSeeder extends Seeder
{
    /**
     * The current Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Create a new seeder instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Get a new Faker instance.
     *
     * @return \Faker\Generator
     */
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Feedback::factory()->count(5)->create(['type' => true]);

        for ($i = 0; $i < 5; $i++) {
            Feedback::factory()->create(['type' => false, 'rating' => $this->faker->numberBetween(1, 10)]);
        }
    }
}
