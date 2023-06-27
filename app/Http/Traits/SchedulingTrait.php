<?php

namespace App\Http\Traits;

trait SchedulingTrait
{
    /**
     * Calculate service slots and cleanup breaks.
     *
     * @param  string $start_time - Start time of the schedule in 'H:i:s' format
     * @param  string $end_time - End time of the schedule in 'H:i:s' format
     * @param  int $duration_minutes - Duration of the service in minutes
     * @param  int $cleanup_break_minutes - Duration of the cleanup break in minutes
     * @param  object $breakTimes - Duration of the cleanup break starttime and endtime in minutes
     * @return array
     */
    public function calculateSlotsAndBreaks(string $start_time, string $end_time, int $duration_minutes, int $cleanup_break_minutes, object $breakTimes): array
    {
        $slots = [];
        $breaks = [];

        $current_time = strtotime($start_time);
        $end_time = strtotime($end_time);

        while ($current_time < $end_time) {
            // Calculate slot end time
            $slot_end_time = $current_time + $duration_minutes * 60;

            // Find any break time that overlaps with the current slot
            $overlappingBreak = $breakTimes->first(function ($breakTime) use ($current_time, $slot_end_time) {
                return $current_time < strtotime($breakTime->break_end) && $slot_end_time > strtotime($breakTime->break_start);
            });

            if ($overlappingBreak) {
                // If there's an overlapping break, adjust the slot end time and skip to the next iteration
                $slot_end_time = strtotime($overlappingBreak->break_start);
            } else {
                // If there's no overlapping break, add the slot to the list
                $slots[] = ['start_time' => date('H:i:s', $current_time), 'end_time' => date('H:i:s', $slot_end_time)];
            }

            // Move to next slot/break
            $current_time = $slot_end_time;

            // Calculate break end time
            $break_end_time = $current_time + $cleanup_break_minutes * 60;

            // Find any break time that overlaps with the current break
            $overlappingBreak = $breakTimes->first(function ($breakTime) use ($current_time, $break_end_time) {
                return $current_time < strtotime($breakTime->break_end) && $break_end_time > strtotime($breakTime->break_start);
            });

            if ($overlappingBreak) {
                // If there's an overlapping break, adjust the break end time and skip to the next iteration
                $break_end_time = strtotime($overlappingBreak->break_end);
            } else if ($break_end_time < $end_time) {
                // If there's no overlapping break and the break end time doesn't exceed the end time, add the break to the list
                $breaks[] = ['start_time' => date('H:i:s', $current_time), 'end_time' => date('H:i:s', $break_end_time)];
            }

            // Move to next slot/break
            $current_time = $break_end_time;
        }

        return ['slots' => $slots, 'breaks' => $breaks];
    }

    public function getMatchingDates($date)
    {
        $matchingDates = [];

        $timestamp = strtotime($date);
        $currentDayOfWeek = date('N', $timestamp); // Get the day of the week (1 for Monday, 7 for Sunday)

        // Calculate the timestamp of the Monday of the same week
        $mondayTimestamp = $timestamp - (($currentDayOfWeek - 1) * 86400); // 86400 seconds in a day

        // Iterate through each day of the week (Monday to Sunday)
        for ($i = 0; $i < 7; $i++) {
            $matchingDates[] = date('Y-m-d', $mondayTimestamp + ($i * 86400));
        }

        return $matchingDates;
    }

    public function isSlotAvailable($slot, $existingAppointments, $max_clients)
    {
        $slotStartTime = strtotime($slot['start_time']);
        $slotEndTime = strtotime($slot['end_time']);

        foreach ($existingAppointments as $appointment) {
            $appointmentStartTime = strtotime($appointment['start_time']);
            $appointmentEndTime = strtotime($appointment['end_time']);
            $appointmentTotal = $appointment['total'];

            // Check if the slot overlaps with any existing appointment and there are available slots
            if (
                $slotStartTime == $appointmentStartTime && $slotEndTime == $appointmentEndTime
                && $appointmentTotal == $max_clients
            ) {
                return false; // Slot is not available
            } elseif ($slotStartTime == $appointmentStartTime && $slotEndTime == $appointmentEndTime) {
                return  $max_clients - $appointmentTotal;
            }
        }
        return $max_clients;
    }
}
