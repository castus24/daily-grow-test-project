<?php

namespace Database\Factories;

use App\Models\Mailing;
use Illuminate\Database\Eloquent\Factories\Factory;

class MailingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Mailing::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'content' => $this->faker->paragraph(3),
            'status' => $this->faker->randomElement(['draft', 'scheduled', 'sent', 'failed']),
            'total_recipients' => $this->faker->numberBetween(10, 100),
            'sent_count' => $this->faker->numberBetween(0, 100),
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 month'),
        ];
    }
} 