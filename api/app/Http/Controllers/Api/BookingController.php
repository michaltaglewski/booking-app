<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetUserBookingsRequest;
use App\Http\Requests\Api\PostCreateBookingRequest;
use App\Models\Booking;
use App\Models\User;
use App\Repositories\BookingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingRepository $bookingRepository
    ) {
    }

    public function index(GetUserBookingsRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $bookings = $this->bookingRepository->getAllForUser($user->id);

        return response()->json($bookings);
    }

    public function store(PostCreateBookingRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();

        $booking = new Booking();
        $booking->user_id = $user->id;
        $booking->room_id = $validated['room_id'];
        $booking->starts_at = $validated['starts_at'];
        $booking->ends_at = $validated['ends_at'];
        $booking->participants_count = $validated['participants_count'];
        $booking->status = BookingStatus::Pending;

        $this->bookingRepository->save($booking);

        return response()->json(['message' => 'Booking created successfully'], 201);
    }

    public function cancel(Request $request, Booking $booking)
    {
        // @TODO
    }
}
