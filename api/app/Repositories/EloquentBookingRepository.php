<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Booking;

class EloquentBookingRepository implements BookingRepository
{
    public function getAllForUser(int $userId): array
    {
        return Booking::query()
            ->where('user_id', $userId)
            ->orderBy('starts_at', 'asc')
            ->get();
    }

    public function save(Booking $booking): void
    {
        $booking->save();
    }
}
