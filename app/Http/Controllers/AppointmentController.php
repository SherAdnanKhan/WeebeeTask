<?php

namespace App\Http\Controllers;

use DateTime;
use Exception;
use DateInterval;
use App\Models\User;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\TimeBreak;
use App\Models\Appointment;
use App\Models\ServiceSchedule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Traits\SchedulingTrait;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\AppointmentsService;
use App\Http\Requests\CreateAppointmentRequest;

class AppointmentController extends Controller
{
    use SchedulingTrait;

    private $appointmentsService;

    public function __construct(AppointmentsService $appointmentsService)
    {
        $this->appointmentsService = $appointmentsService;
    }

    public function index($service_id, $date)
    {
        $availableSlots = $this->appointmentsService->index($service_id, $date);

        return response()->json($availableSlots);
    }

    public function store(CreateAppointmentRequest $request)
    {
        $availableSlots = $this->appointmentsService->store($request);

        return response()->json($availableSlots);
    }

    // public function store(CreateAppointmentRequest $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $service = Service::find($request->service_id);

    //         $appointmentStartTime = DateTime::createFromFormat('Y-m-d H:i:s', $request->appointment_start_time);
    //         $appointmentEndTime = clone $appointmentStartTime;
    //         $appointmentEndTime->modify("+{$service->duration_minutes} minutes");

    //         $dayOfWeek = $appointmentStartTime->format('l');

    //         $serviceSchedule = ServiceSchedule::where('service_id', $service->id)
    //             ->where('day_of_week', $dayOfWeek)
    //             ->first();

    //         if (!$serviceSchedule) {
    //             throw new Exception('The service is not available on the chosen day');
    //         }

    //         $now = new DateTime();
    //         $maxDate = (clone $now)->add(new DateInterval("P{$service->booking_days_in_advance}D"));
    //         $appointmentDate = clone $appointmentStartTime;

    //         if ($appointmentDate < $now || $appointmentDate > $maxDate) {
    //             throw new Exception('The requested date is outside the bookable window');
    //         }

    //         // Check if the appointment time falls on a holiday
    //         $holiday = Holiday::where('service_id', $service->id)
    //             ->where('date', $appointmentStartTime->format('Y-m-d'))
    //             ->where(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
    //                 $query->where(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
    //                     $query->whereNull('start_time')->whereNull('end_time')
    //                         ->orWhere(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
    //                             $query->where(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
    //                                 $query->where(function ($query) use ($appointmentStartTime) {
    //                                     $query->where('start_time', '<=', $appointmentStartTime->format('H:i:s'))
    //                                         ->where('end_time', '>=', $appointmentStartTime->format('H:i:s'));
    //                                 })->orWhere(function ($query) use ($appointmentEndTime) {
    //                                     $query->where('start_time', '<=', $appointmentEndTime->format('H:i:s'))
    //                                         ->where('end_time', '>=', $appointmentEndTime->format('H:i:s'));
    //                                 })->orWhere(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
    //                                     $query->where('start_time', '>=', $appointmentStartTime->format('H:i:s'))
    //                                         ->where('end_time', '<=', $appointmentEndTime->format('H:i:s'));
    //                                 });
    //                             });
    //                         });
    //                 });
    //             })
    //             ->first();

    //         if ($holiday) {
    //             throw new Exception('The service is not available at the chosen time due to a holiday');
    //         }

    //         // Check if the appointment time is within the service's operating hours
    //         $appointmentStartTimeStr = $appointmentStartTime->format('H:i:s');
    //         $appointmentEndTimeStr = $appointmentEndTime->format('H:i:s');
    //         $start_time = $serviceSchedule->start_time;
    //         $end_time = $serviceSchedule->end_time;

    //         if (
    //             strcmp($appointmentStartTimeStr, $start_time) < 0 ||
    //             strcmp($appointmentEndTimeStr, $end_time) > 0
    //         ) {
    //             throw new Exception('The appointment time is outside the service\'s operating hours');
    //         }

    //         // Check if the appointment time conflicts with the service's breaks
    //         $breakTimes = TimeBreak::where('service_id', $service->id)->get();

    //         foreach ($breakTimes as $breakTime) {
    //             $breakStartTimeStr = $breakTime->break_start;
    //             $breakEndTimeStr = $breakTime->break_end;

    //             if (
    //                 ($appointmentStartTimeStr > $breakStartTimeStr && $appointmentStartTimeStr < $breakEndTimeStr)
    //                 || ($appointmentEndTimeStr > $breakStartTimeStr && $appointmentEndTimeStr < $breakEndTimeStr)
    //             ) {
    //                 throw new Exception('The appointment time conflicts with the service\'s break time');
    //             }
    //         }

    //         $schedulesAndBreaks = $this->calculateSlotsAndBreaks(
    //             $serviceSchedule->start_time,
    //             $serviceSchedule->end_time,
    //             $service->duration_minutes,
    //             $service->cleanup_break_minutes,
    //             $breakTimes
    //         );

    //         $breaks = $schedulesAndBreaks['breaks'];
    //         // Check if requested slot falls between configured break between appointments
    //         foreach ($breaks as $break) {
    //             if (
    //                 ($appointmentStartTimeStr > $break['start_time'] && $appointmentStartTimeStr < $break['end_time']) ||
    //                 ($appointmentEndTimeStr > $break['start_time'] && $appointmentEndTimeStr < $break['end_time']) ||
    //                 ($break['start_time'] > $appointmentStartTimeStr && $break['start_time'] < $appointmentEndTimeStr) ||
    //                 ($break['end_time'] > $appointmentStartTimeStr && $break['end_time'] < $appointmentEndTimeStr)
    //             ) {
    //                 throw new Exception('The appointment time conflicts with the service\'s break time');
    //             }
    //         }

    //         // Check if the appointment time conflicts with existing appointments for the service
    //         $conflictingAppointments = Appointment::where('service_id', $request->service_id)
    //             ->where(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
    //                 $query->whereBetween('start_time', [$appointmentStartTime, $appointmentEndTime])
    //                     ->orWhereBetween('end_time', [$appointmentStartTime, $appointmentEndTime]);
    //             })->lockForUpdate()->get();

    //         if ($conflictingAppointments->count() + count($request->users) > $service->max_clients) {
    //             throw new Exception("The number of users exceeds the available slots for this appointment.");
    //         }

    //         // Create the appointments
    //         foreach ($request->users as $userData) {
    //             $user = User::firstOrCreate([
    //                 'email' => $userData['email'],
    //             ], [
    //                 'first_name' => $userData['first_name'],
    //                 'last_name' => $userData['last_name'],
    //             ]);

    //             $appointment = new Appointment;
    //             $appointment->service_id = $request->service_id;
    //             $appointment->user_id = $user->id;
    //             $appointment->start_time = $appointmentStartTime;
    //             $appointment->end_time = $appointmentEndTime;
    //             $appointment->save();
    //         }

    //         DB::commit();

    //         return response()->json(['success' => 'Appointments booked successfully']);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }
    // }
}
