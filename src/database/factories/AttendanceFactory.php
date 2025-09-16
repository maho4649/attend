<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'clock_in' => $this->faker->dateTimeThisMonth,
            'clock_out' => $this->faker->dateTimeThisMonth('+1 hour'),
            'description' => $this->faker->sentence,
            'status' => 'pending',
        ];
    }
}
