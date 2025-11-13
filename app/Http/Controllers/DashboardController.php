<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Swagger\SwaggerDashboardDocs;
use App\Services\User\UserService;
use App\Services\Appointment\AppointmentService;
use App\Services\Customer\CustomerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use SwaggerDashboardDocs;

    public function __construct(
        protected UserService $userService,
        protected AppointmentService $appointmentService,
        protected CustomerService $customerService
    ) {}

    public function index(Request $request): \Inertia\Response|JsonResponse
    {
        // Período customizado ou padrão (mês atual)
        $period = [
            'start_date' => Carbon::parse($request->input('start_date', now()->startOfMonth()->format('Y-m-d')))->format('Y-m-d'),
            'end_date' => Carbon::parse($request->input('end_date', now()->endOfMonth()->format('Y-m-d')))->format('Y-m-d'),
        ];

        // Período do dia atual para agendamentos
        $todayPeriod = [
            'start_date' => now()->startOfDay()->format('Y-m-d'),
            'end_date' => now()->endOfDay()->format('Y-m-d'),
        ];

        // Buscar dados do dashboard
        $ranking = $this->userService->getRanking($period);
        $totalUsers = $this->userService->getTotalUsersCount($period);
        $totalCustomers = $this->customerService->getTotalCustomersCount($period);
        $appointmentsChart = $this->appointmentService->getAppointmentsInPeriodCount($period);
        $appointments = $this->appointmentService->getAppointmentsInPeriodFromDash($todayPeriod);
        $mostPopularServices = $this->appointmentService->getMostPopularServices($period);

        $dashboardData = [
            'ranking' => $ranking,
            'totalUsers' => $totalUsers,
            'totalCustomers' => $totalCustomers,
            'appointments' => $appointments,
            'appointmentsChart' => $appointmentsChart,
            'mostPopularServices' => $mostPopularServices,
            'period' => $period,
            'userName' => $request->user()->name,
        ];

        if ($request->wantsJson()) {
            // Para API: retorna JSON com os dados do dashboard
            return response()->json([
                'data' => $dashboardData,
            ], 200);
        }

        // Para Web: renderiza página Inertia
        return Inertia::render('dashboard/index', $dashboardData);
    }
}
