<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetUserBookingsRequest;
use App\Http\Requests\Api\PatchCancelBookingRequest;
use App\Http\Requests\Api\PostCreateBookingRequest;
use App\Http\Resources\Api\BookingCollection;
use App\Http\Resources\Api\BookingResource;
use App\Models\Booking;
use App\Models\User;
use App\Repositories\BookingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
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

        return new BookingCollection($bookings);
    }

    public function store(PostCreateBookingRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();

        $startsAt = Carbon::parse($validated['starts_at']);
        $endsAt = Carbon::parse($validated['ends_at']);

        if ($this->bookingRepository->existsActiveBookingForRoomWithinDateRange($validated['room_id'], $startsAt, $endsAt)) {
            return response()->json(['message' => 'Booking conflicts with existing booking'], 409);
        }

        $booking = new Booking();
        $booking->user_id = $user->id;
        $booking->room_id = $validated['room_id'];
        $booking->starts_at = $startsAt;
        $booking->ends_at = $endsAt;
        $booking->participants_count = $validated['participants_count'];
        $booking->status = BookingStatus::Pending;

        $this->bookingRepository->save($booking);

        $bookingResource = new BookingResource($booking);

        return $bookingResource->response()->setStatusCode(201);
    }

    public function cancel(PatchCancelBookingRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $booking = $this->bookingRepository->getById($request->validated('id'));
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        if ($booking->user_id !== $user->id) {
            abort(403, 'Unauthorized to cancel this booking');
        }

        if ($booking->status === BookingStatus::Cancelled) {
            return response()->json(['message' => 'Booking is already cancelled'], 400);
        }

        $booking->status = BookingStatus::Cancelled;
        $this->bookingRepository->save($booking);

        $bookingResource = new BookingResource($booking);

        return $bookingResource->response()->setStatusCode(200);
    }
}
