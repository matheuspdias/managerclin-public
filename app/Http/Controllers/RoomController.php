<?php

namespace App\Http\Controllers;

use App\DTO\Room\CreateRoomDTO;
use App\DTO\Room\UpdateRoomDTO;
use App\Http\Controllers\Traits\Swagger\SwaggerRoomDocs;
use App\Http\Requests\Room\CreateRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Http\Resources\Room\RoomResource;
use Illuminate\Http\Request;
use App\Services\Room\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class RoomController extends Controller
{
    use SwaggerRoomDocs;

    public function __construct(
        protected RoomService $service,
    ) {}

    public function index(Request $request): \Inertia\Response|JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', null);
        $perPage = (int) $request->get('per_page', 10);
        $orderBy = $request->get('order', 'name:asc');

        $rooms = $this->service->getAllPaginate($search, $page, $perPage, $orderBy);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => RoomResource::collection($rooms->items()),
                'meta' => [
                    'current_page' => $rooms->currentPage(),
                    'last_page' => $rooms->lastPage(),
                    'per_page' => $rooms->perPage(),
                    'total' => $rooms->total(),
                ],
            ]);
        }

        return Inertia::render('room/index', [
            'rooms' => $rooms,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'order' => $orderBy,
                'page' => $page,
            ],
        ]);
    }

    public function show(int $id, Request $request): \Inertia\Response|JsonResponse
    {
        $room = $this->service->getById($id);

        if ($request->wantsJson()) {
            return (new RoomResource($room))
                ->response()
                ->setStatusCode(200);
        }

        return Inertia::render('room/show', [
            'room' => $room,
        ]);
    }

    public function store(CreateRoomRequest $request): JsonResponse|RedirectResponse
    {
        $room = $this->service->store(CreateRoomDTO::makeFromRequest($request));

        if ($request->wantsJson()) {
            return (new RoomResource($room))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('rooms.index')
            ->with('success', 'Room created successfully');
    }

    public function update(int $id, UpdateRoomRequest $request): JsonResponse|RedirectResponse
    {
        $room = $this->service
            ->update($id, UpdateRoomDTO::makeFromRequest($request));

        if ($request->wantsJson()) {
            return (new RoomResource($room))
                ->response()
                ->setStatusCode(200);
        }

        return redirect()->route('rooms.index')
            ->with('success', 'Room updated successfully');
    }

    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $this->service->destroy($id);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('rooms.index')
            ->with('success', 'Room deleted successfully');
    }
}
