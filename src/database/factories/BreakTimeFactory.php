<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'clock_in' => Carbon::today()->setHour($this->faker->numberBetween(10, 12))->setMinute(0),
            'clock_out' => Carbon::today()->setHour($this->faker->numberBetween(12, 14))->setMinute(0),
        ];
    }
}
