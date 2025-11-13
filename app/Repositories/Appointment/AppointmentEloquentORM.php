<?php

namespace App\Repositories\Appointment;

use App\Enums\AppointmentStatusEnum;
use App\Models\Appointment;
use App\Repositories\Appointment\AppointmentRepositoryInterface;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentEloquentORM extends BaseRepository implements AppointmentRepositoryInterface
{
    protected array $searchable = [
        'user.name',
        'customer.name',
    ];

    protected array $sortable = [
        'date'
    ];

    protected array $relations = [
        'user',
        'customer',
        'service',
        'room',
    ];

    public function __construct()
    {
        parent::__construct(new Appointment());
    }

    public function getTotalSales(array $period): float
    {
        return $this->model->where('status', AppointmentStatusEnum::COMPLETED)
            ->when(!empty($period['start_date']), function ($query) use ($period) {
                $query->whereDate('date', '>=', $period['start_date']);
            })
            ->when(!empty($period['end_date']), function ($query) use ($period) {
                $query->whereDate('date', '<=', $period['end_date']);
            })
            ->when(!Auth::user()->isAdmin(), function ($query) {
                $query->where('id_user', Auth::id());
            })
            ->whereNotNull('price')
            ->sum('price');
    }

    public function getAppointmentsInPeriodCount(array $period, ?string $status = null): int
    {
        $query = $this->model->whereDate('date', '>=', $period['start_date'])
            ->whereDate('date', '<=', $period['end_date']);

        if ($status) {
            $query->where('status', $status);
        }
        $query->when(!Auth::user()->isAdmin(), function ($query) {
            $query->where('id_user', Auth::id());
        });

        return $query->count();
    }

    public function getAppointmentsInPeriodCompletedCount(array $period): int
    {
        return $this->model->whereDate('date', '>=', $period['start_date'])
            ->whereDate('date', '<=', $period['end_date'])
            ->where('status', AppointmentStatusEnum::COMPLETED)
            ->when(!Auth::user()->isAdmin(), function ($query) {
                $query->where('id_user', Auth::id());
            })
            ->count();
    }

    public function getAppointmentsInPeriod(array $period, string|null $search = null, int $page, int $perPage, string $order): LengthAwarePaginator
    {
        $query = $this->model
            ->select(
                'id',
                'id_user',
                'id_service',
                'id_customer',
                'id_room',
                'date',
                'status',
                'notes',
                'start_time',
                'end_time'
            )
            //with relationships id and name
            ->with([
                'user:id,name',
                'service:id,name',
                'customer:id,name,',
                'room:id,name'
            ])
            ->whereDate('date', '>=', $period['start_date'])
            ->whereDate('date', '<=', $period['end_date'])
            ->whereHas('customer')  // Garante que o customer não está deletado
            ->whereHas('user');

        if (!Auth::user()->isAdmin()) {
            $query->where('id_user', Auth::id());
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Order by
        if ($order) {
            $orderParts = explode(':', $order);
            $column = $orderParts[0];
            $direction = $orderParts[1] ?? 'asc';
            if (in_array($column, $this->sortable)) {
                $query->orderBy($column, $direction);
            }
        } else {
            $query->orderBy('date', 'asc');
        }

        return $query->with(['service', 'customer', 'room'])
            ->orderBy('date', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getAppointmentsInPeriodFromDash(array $period): Collection
    {
        $query = $this->model
            ->select(
                'id',
                'id_user',
                'id_service',
                'id_customer',
                'id_room',
                'date',
                'status',
                'notes',
                'start_time',
                'end_time'
            )
            ->whereDate('date', '>=', $period['start_date'])
            ->whereDate('date', '<=', $period['end_date'])
            ->whereHas('customer')  // Garante que o customer não está deletado
            ->whereHas('user');

        if (!Auth::user()->isAdmin()) {
            $query->where('id_user', Auth::id());
        }
        return $query->with(['user.role', 'service', 'customer', 'room'])
            ->orderBy('date', 'asc')
            ->get()
            ->take(3);
    }

    public function getAppointmentsToNotify(string $date, int $idCompany, ?string $notificationType = null): Collection
    {
        $query = $this->model
            ->withoutGlobalScopes()
            ->whereDate('date', $date)
            ->where('status', AppointmentStatusEnum::SCHEDULED)
            ->with(['customer', 'service', 'user'])
            ->where('id_company', $idCompany);

        // Filtrar apenas os que ainda não foram notificados do tipo especificado
        if ($notificationType === 'day_before') {
            $query->whereNull('notified_day_before_at');
        } elseif ($notificationType === 'same_day') {
            $query->whereNull('notified_same_day_at');
        }

        return $query->get();
    }

    public function getMostPopularServices(array $period): Collection
    {
        // last 30 days
        return $this->model
            ->select('id_service', DB::raw('count(*) as total_appointments'))
            ->whereDate('date', '>=', $period['start_date'])
            ->whereDate('date', '<=', $period['end_date'])
            ->where('status', AppointmentStatusEnum::COMPLETED)
            ->groupBy('id_service')
            ->orderByDesc('total_appointments')
            ->with('service')
            ->take(7)
            ->get();
    }

    public function hasAppointmentConflict(int $idUser, string $date, string $startTime, string $endTime, ?int $excludeAppointmentId = null): bool
    {
        $query = $this->model
            ->where('id_user', $idUser)
            ->where('date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->exists();
    }

    public function createMany($appointments): array
    {
        $createdAppointments = [];
        foreach ($appointments as $appointment) {
            $createdAppointment = $this->model->withoutGlobalScopes()->create($appointment);
            $createdAppointments[] = $createdAppointment;
        }
        return $createdAppointments;
    }

    public function getAllPaginated(array $filters = [], int $page = 1, int $perPage = 15, ?int $userId = null): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['user:id,name', 'service:id,name,price', 'customer:id,name,phone', 'room:id,name']);

        // Apply user filter for non-admin users
        if ($userId) {
            $query->where('id_user', $userId);
        }

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('service', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id']) && $filters['user_id'] !== 'all') {
            $query->where('id_user', $filters['user_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getForPeriod(string $startDate, string $endDate, ?int $userId = null): Collection
    {
        $query = $this->model
            ->with(['user:id,name', 'service:id,name,price', 'customer:id,name,phone', 'room:id,name'])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if ($userId) {
            $query->where('id_user', $userId);
        }

        return $query->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    public function updateStatus(int $id, string $status): Appointment
    {
        $appointment = $this->findById($id);
        $appointment->update(['status' => $status]);
        return $appointment->fresh(['user', 'service', 'customer', 'room']);
    }

    public function getTimeConflicts(string $date, string $startTime, string $endTime, int $userId, int $roomId, ?int $appointmentId = null): array
    {
        // Verificar apenas conflitos de profissional (horário), não de consultório
        $query = $this->model
            ->with(['customer:id,name', 'service:id,name'])
            ->where('date', $date)
            ->where('id_user', $userId) // Apenas verificar conflito de profissional
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($appointmentId) {
            $query->where('id', '!=', $appointmentId);
        }

        return $query->get()->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'customer_name' => $appointment->customer->name,
                'service_name' => $appointment->service->name ?? '',
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'type' => 'user', // Sempre será conflito de profissional
            ];
        })->toArray();
    }

    public function getAppointmentsForDate(string $date, int $userId): Collection
    {
        return $this->model
            ->where('date', $date)
            ->where('id_user', $userId)
            ->where('status', '!=', AppointmentStatusEnum::CANCELLED)
            ->orderBy('start_time')
            ->get(['id', 'start_time', 'end_time']);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->with(['user:id,name', 'service:id,name,price', 'customer:id,name,phone', 'room:id,name'])
            ->where('id_user', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();
    }
}
