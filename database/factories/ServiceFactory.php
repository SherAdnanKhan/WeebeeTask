<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'duration_minutes' => $this->faker->numberBetween(30, 120),
            'max_clients' => $this->faker->numberBetween(1, 10),
            'slot_interval_minutes' => $this->faker->numberBetween(5, 30),
            'cleanup_break_minutes' => $this->faker->numberBetween(5, 30),
            'booking_days_in_advance' => $this->faker->numberBetween(1, 7),
        ];
    }
}
