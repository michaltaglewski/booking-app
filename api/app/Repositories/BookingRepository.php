<?php

declare(strict_types=1);

namespace App\Repositories;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Booking;

interface BookingRepository
{
    public function getById(string $id): ?Booking;

    public function getAllForUser(int $userId): Collection;

    public function existsActiveBookingForRoomWithinDateRange(string $roomId, DateTimeInterface $startsAt, DateTimeInterface $endsAt): bool;

    public function save(Booking $booking): void;
}
