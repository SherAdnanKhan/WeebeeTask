<?php

namespace App\Http\Services;

use DateTime;
use Exception;
use DateInterval;
use App\Http\Traits\SchedulingTrait;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\HolidayRepository;
use App\Http\Repositories\ServiceRepository;
use App\Http\Repositories\TimeBreakRepository;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\ServiceScheduleRepository;

class AppointmentsService
{
    use SchedulingTrait;

    private $appointmentRepository;
    private $serviceScheduleRepository;
    private $holidayRepository;
    private $serviceRepository;
    private $timeBreakRepository;
    private $userRepository;

    public function __construct(
        AppointmentRepository $appointmentRepository,
        ServiceScheduleRepository $serviceScheduleRepository,
        HolidayRepository $holidayRepository,
        ServiceRepository $serviceRepository,
        TimeBreakRepository $timeBreakRepository,
        UserRepository $userRepository
    ) {
        $this->appointmentRepository = $appointmentRepository;
        $this->serviceScheduleRepository = $serviceScheduleRepository;
        $this->holidayRepository = $holidayRepository;
        $this->serviceRepository = $serviceRepository;
        $this->timeBreakRepository = $timeBreakRepository;
        $this->userRepository = $userRepository;
    }

    public function index($service_id, $date)
    {
        try {
            $serviceSchedules = $this->serviceScheduleRepository->getServiceSchedules($service_id);
            $weekDates = $this->getMatchingDates($date);
            $holidays = $this->holidayRepository->getHolidays($service_id, $weekDates);
            $existingAppointments = $this->appointmentRepository->getAppointments($service_id, $weekDates);

            $weeklyWiseSlot = $this->calculateWeeklyWiseSlot($serviceSchedules, $holidays, $existingAppointments);

            return ['weekly_wise_slot' => $weeklyWiseSlot];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function calculateWeeklyWiseSlot($serviceSchedules, $holidays, $existingAppointments)
    {
        $weeklyWiseSlot = [];

        foreach ($serviceSchedules as $serviceSchedule) {
            $availableSlots = $this->calculateAvailableSlots($serviceSchedule, $holidays, $existingAppointments);
            $weeklyWiseSlot[] = [$serviceSchedule->day_of_week, $availableSlots];
        }

        return $weeklyWiseSlot;
    }

    private function calculateAvailableSlots($serviceSchedule, $holidays, $existingAppointments)
    {
        $timeBreaks = $this->getTimeBreaks($serviceSchedule, $holidays);
        $schedulesAndBreaks = $this->calculateSlotsAndBreaks(
            $serviceSchedule->start_time,
            $serviceSchedule->end_time,
            $serviceSchedule->service->duration_minutes,
            $serviceSchedule->service->cleanup_break_minutes,
            $timeBreaks
        );

        $availableSlots = $schedulesAndBreaks['slots'];

        if (array_key_exists($serviceSchedule->day_of_week, $existingAppointments)) {
            $appointments = $existingAppointments[$serviceSchedule->day_of_week];
            foreach ($availableSlots as &$slot) {
                $slot['is_available'] = $this->isSlotAvailable($slot, $appointments, $serviceSchedule->service->max_clients);
            }
        } else {
            foreach ($availableSlots as &$slot) {
                $slot['is_available'] = $serviceSchedule->service->max_clients;
            }
        }

        return $availableSlots;
    }

    private function getTimeBreaks($serviceSchedule, $holidays)
    {
        if (array_key_exists($serviceSchedule->day_of_week, $holidays)) {
            $holiday = $holidays[$serviceSchedule->day_of_week];
            return collect([
                (object) [
                    'break_start' => $holiday['start_time'],
                    'break_end' => $holiday['end_time']
                ]
            ]);
        } else {
            return $serviceSchedule->service->timeBreaks;
        }
    }

    public function store($request)
    {
        try {
            $service = $this->serviceRepository->getService($request->service_id);
            $appointmentStartTime = DateTime::createFromFormat('Y-m-d H:i:s', $request->appointment_start_time);
            $appointmentEndTime = clone $appointmentStartTime;
            $appointmentEndTime->modify("+{$service->duration_minutes} minutes");
            $dayOfWeek = $appointmentStartTime->format('l');

            $serviceSchedule = $this->serviceScheduleRepository->getServiceScheduleByDay($service->id, $dayOfWeek);
            $this->checkServiceAvailability($serviceSchedule);

            $this->checkDateWithinBookableWindow($service, $appointmentStartTime);

            $this->checkIfAppointmentOnHoliday($service, $appointmentStartTime, $appointmentEndTime);

            $this->checkAppointmentWithinOperatingHours($serviceSchedule, $appointmentStartTime, $appointmentEndTime);

            $this->checkAppointmentAgainstBreakTimes($service, $appointmentStartTime, $appointmentEndTime, $serviceSchedule);

            $this->checkAppointmentSlotAvailability($service, $appointmentStartTime, $appointmentEndTime, $request->users);

            $this->createAppointments($request, $service, $appointmentStartTime, $appointmentEndTime);

            return ['success' => 'Appointment stored successfully'];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function checkServiceAvailability($serviceSchedule)
    {
        if (!$serviceSchedule) {
            throw new Exception('The service is not available on the chosen day');
        }
    }

    private function checkDateWithinBookableWindow($service, $appointmentStartTime)
    {
        $now = new DateTime();
        $maxDate = (clone $now)->add(new DateInterval("P{$service->booking_days_in_advance}D"));
        $appointmentDate = clone $appointmentStartTime;

        if ($appointmentDate < $now || $appointmentDate > $maxDate) {
            throw new Exception('The requested date is outside the bookable window');
        }
    }

    private function checkIfAppointmentOnHoliday($service, $appointmentStartTime, $appointmentEndTime)
    {
        $holiday = $this->holidayRepository->getHoliday($service->id, $appointmentStartTime, $appointmentEndTime);

        if ($holiday) {
            throw new Exception('The service is not available at the chosen time due to a holiday');
        }
    }

    private function checkAppointmentWithinOperatingHours($serviceSchedule, $appointmentStartTime, $appointmentEndTime)
    {
        $appointmentEndTimeClone = clone $appointmentEndTime;
        $appointmentStartTimeClone = clone $appointmentStartTime;
        $appointmentStartTimeStr = $appointmentEndTimeClone->format('H:i:s');
        $appointmentEndTimeStr = $appointmentStartTimeClone->format('H:i:s');
        $start_time = $serviceSchedule->start_time;
        $end_time = $serviceSchedule->end_time;

        if (
            strcmp($appointmentStartTimeStr, $start_time) < 0 ||
            strcmp($appointmentEndTimeStr, $end_time) > 0
        ) {
            throw new Exception('The appointment time is outside the service\'s operating hours');
        }
    }

    private function checkAppointmentAgainstBreakTimes($service, $appointmentStartTime, $appointmentEndTime, $serviceSchedule)
    {
        $appointmentEndTimeClone = clone $appointmentEndTime;
        $appointmentStartTimeClone = clone $appointmentStartTime;
        $appointmentStartTimeStr = $appointmentStartTimeClone->format('H:i:s');
        $appointmentEndTimeStr = $appointmentEndTimeClone->format('H:i:s');

        $breakTimes = $this->timeBreakRepository->getTimeBreaks($service->id);
        foreach ($breakTimes as $breakTime) {
            $breakStartTimeStr = $breakTime->break_start;
            $breakEndTimeStr = $breakTime->break_end;
            if (
                ($appointmentStartTimeStr > $breakStartTimeStr && $appointmentStartTimeStr < $breakEndTimeStr) ||
                ($appointmentEndTimeStr > $breakStartTimeStr && $appointmentEndTimeStr < $breakEndTimeStr)
            ) {
                throw new Exception('The appointment time conflicts with the break time');
            }
        }

        $schedulesAndBreaks = $this->calculateSlotsAndBreaks(
            $serviceSchedule->start_time,
            $serviceSchedule->end_time,
            $service->duration_minutes,
            $service->cleanup_break_minutes,
            $breakTimes
        );

        $breaks = $schedulesAndBreaks['breaks'];
        // Check if requested slot falls between configured break between appointments
        foreach ($breaks as $break) {
            if (
                ($appointmentStartTimeStr > $break['start_time'] && $appointmentStartTimeStr < $break['end_time']) ||
                ($appointmentEndTimeStr > $break['start_time'] && $appointmentEndTimeStr < $break['end_time']) ||
                ($break['start_time'] > $appointmentStartTimeStr && $break['start_time'] < $appointmentEndTimeStr) ||
                ($break['end_time'] > $appointmentStartTimeStr && $break['end_time'] < $appointmentEndTimeStr)
            ) {
                throw new Exception('The appointment time conflicts with the service\'s break time');
            }
        }
    }

    private function checkAppointmentSlotAvailability($service, $appointmentStartTime, $appointmentEndTime, $users)
    {
        $conflictingAppointments = $this->appointmentRepository->getConflictingAppointments($service->id, $appointmentStartTime, $appointmentEndTime);

        if ($conflictingAppointments->count() + count($users) > $service->max_clients) {
            throw new Exception("The number of users exceeds the available slots for this appointment.");
        }
    }

    private function createAppointments($request, $service, $appointmentStartTime, $appointmentEndTime)
    {
        foreach ($request->users as $userData) {
            $user = $this->userRepository->firstOrCreate(['email' => $userData['email']], [
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
            ]);
            $this->appointmentRepository->createAppointment($user, $service->id, $appointmentStartTime, $appointmentEndTime);
        }
    }
}
