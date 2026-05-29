<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\RoomRepository;

class RoomController extends Controller
{
    public function __construct(
        private readonly RoomRepository $roomRepository
    ) {
    }

    public function index()
    {
        $rooms = $this->roomRepository->getAvailable();

        return response()->json($rooms);
    }
}
