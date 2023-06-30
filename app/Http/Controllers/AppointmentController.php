<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\SchedulingTrait;
use App\Http\Services\AppointmentsService;
use App\Http\Requests\CreateAppointmentRequest;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    use SchedulingTrait;

    private $appointmentsService;

    /**
     * AppointmentController constructor.
     *
     * @param AppointmentsService $appointmentsService - Appointments service instance
     */
    public function __construct(AppointmentsService $appointmentsService)
    {
        $this->appointmentsService = $appointmentsService;
    }

    /**
     * Get the available appointment slots for a specific service and date.
     *
     * @param int $service_id - ID of the service
     * @param string $date - Date in 'Y-m-d' format
     * @return JsonResponse - JSON response containing available slots
     */
    public function index(int $service_id, string $date): JsonResponse
    {
        $availableSlots = $this->appointmentsService->index($service_id, $date);

        return response()->json($availableSlots);
    }

    /**
     * Store a new appointment.
     *
     * @param CreateAppointmentRequest $request - Create appointment request object
     * @return JsonResponse - JSON response containing success or error message
     */
    public function store(CreateAppointmentRequest $request): JsonResponse
    {
        $availableSlots = $this->appointmentsService->store($request);

        return response()->json($availableSlots);
    }
}
