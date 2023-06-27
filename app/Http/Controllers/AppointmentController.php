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
}
