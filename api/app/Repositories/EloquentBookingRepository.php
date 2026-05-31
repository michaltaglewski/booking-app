<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Booking;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentBookingRepository implements BookingRepository
{
    public function getById(string $id): ?Booking
    {
        return Booking::query()->find($id);
    }

    public function getAllForUser(int $userId): Collection
    {
        return Booking::query()
            ->with('room')
            ->where('user_id', $userId)
            ->orderBy('starts_at', 'asc')
            ->get();
    }

    public function existsActiveBookingForRoomWithinDateRange(string $roomId, DateTimeInterface $startsAt, DateTimeInterface $endsAt): bool
    {
        return Booking::query()
            ->where('room_id', $roomId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('starts_at', '>=', $startsAt)
            ->where('ends_at', '<=', $endsAt)
            ->exists();
    }

    public function save(Booking $booking): void
    {
        $booking->save();
    }
}
