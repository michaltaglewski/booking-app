<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetRoomsRequest;
use App\Repositories\RoomRepository;
use Illuminate\Support\Carbon;

class RoomController extends Controller
{
    public function __construct(
        private readonly RoomRepository $roomRepository
    ) {
    }

    public function index(GetRoomsRequest $request)
    {
        $validated = $request->validated();

        $rooms = $this->roomRepository->getAvailable(
            Carbon::parse($validated['starts_at'] ?? null),
            Carbon::parse($validated['ends_at'] ?? null),
        );

        return response()->json($rooms);
    }
}
