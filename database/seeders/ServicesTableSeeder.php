<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\TimeBreak;
use App\Models\ServiceSchedule;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
 
        // Men Haircut
        $menHaircut = Service::create([
            'name' => 'Men Haircut',
            'duration_minutes' => 30,
            'max_clients' => 3,
            'slot_interval_minutes' => 10,
            'cleanup_break_minutes' => 5,
        ]);

        // Create service schedule for Men Haircut
        ServiceSchedule::create([
            'service_id' => $menHaircut->id,
            'day_of_week' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '20:00',
        ]);

        ServiceSchedule::create([
            'service_id' => $menHaircut->id,
            'day_of_week' => 'Tuesday',
            'start_time' => '08:00',
            'end_time' => '20:00',
        ]);

        ServiceSchedule::create([
            'service_id' => $menHaircut->id,
            'day_of_week' => 'Wednesday',
            'start_time' => '08:00',
            'end_time' => '20:00',
        ]);

        ServiceSchedule::create([
            'service_id' => $menHaircut->id,
            'day_of_week' => 'Thursday',
            'start_time' => '08:00',
            'end_time' => '20:00',
        ]);

        ServiceSchedule::create([
            'service_id' => $menHaircut->id,
            'day_of_week' => 'Friday',
            'start_time' => '08:00',
            'end_time' => '20:00',
        ]);

        ServiceSchedule::create([
            'service_id' => $menHaircut->id,
            'day_of_week' => 'Saturday',
            'start_time' => '10:00',
            'end_time' => '22:00',
        ]);

        // Create breaks for Men Haircut
        TimeBreak::create([
            'service_id' => $menHaircut->id,
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);

        TimeBreak::create([
            'service_id' => $menHaircut->id,
            'break_start' => '15:00',
            'break_end' => '16:00',
        ]);

        // Create holiday for Men Haircut
        Holiday::create([
            'service_id' => $menHaircut->id,
            'date' => Carbon::now()->addDays(2),
            'start_time' => null,
            'end_time' => null,
        ]);

        // Woman Haircut
        $womanHaircut = Service::create([
            'name' => 'Woman Haircut',
            'duration_minutes' => 60,
            'max_clients' => 3,
            'slot_interval_minutes' => 60,
            'cleanup_break_minutes' => 10,
        ]);

        // Create service schedule for Woman Haircut
        ServiceSchedule::create([
            'service_id' => $womanHaircut->id,
            'day_of_week' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '20:00',
        ]);

        ServiceSchedule::create([
            'service_id' => $womanHaircut->id,
            'day_of_week' => 'Tuesday',
            'start_time' => '08:00',
            'end_time' => '20:00',
        ]);

        ServiceSchedule::create([
            'service_id' => $womanHaircut->id,
            'day_of_week' => 'Wednesday',
            'start_time' => '08:00',
            'end_time' => '20:00',
        ]);

        ServiceSchedule::create([
            'service_id' => $womanHaircut->id,
            'day_of_week' => 'Thursday',
            'start_time' => '08:00',
            'end_time' => '20:00',
        ]);

        ServiceSchedule::create([
            'service_id' => $womanHaircut->id,
            'day_of_week' => 'Friday',
            'start_time' => '08:00',
            'end_time' => '20:00',
        ]);

        ServiceSchedule::create([
            'service_id' => $womanHaircut->id,
            'day_of_week' => 'Saturday',
            'start_time' => '10:00',
            'end_time' => '22:00',
        ]);

        // Create breaks for Woman Haircut
        TimeBreak::create([
            'service_id' => $womanHaircut->id,
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);

        TimeBreak::create([
            'service_id' => $womanHaircut->id,
            'break_start' => '15:00',
            'break_end' => '16:00',
        ]);

        // Create holiday for Woman Haircut
        Holiday::create([
            'service_id' => $womanHaircut->id,
            'date' => Carbon::now()->addDays(2),
            'start_time' => null,
            'end_time' => null,
        ]);
    }
}
