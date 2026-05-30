<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Booking;

interface BookingRepository
{
    public function getAllForUser(int $userId): array;

    public function save(Booking $booking): void;
}
