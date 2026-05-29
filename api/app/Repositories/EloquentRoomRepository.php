<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\BookingStatus;
use App\Models\Room;
use DateTimeInterface;

class EloquentRoomRepository implements RoomRepository
{
    public function getAvailable(?DateTimeInterface $startsAt = null, ?DateTimeInterface $endsAt = null): array
    {
        $startsAt ??= now();
        $endsAt ??= $startsAt;

        return Room::query()
            ->whereDoesntHave('bookings', function ($query) use ($startsAt, $endsAt) {
                $query->whereIn('status', [
                    BookingStatus::Pending->value,
                    BookingStatus::Confirmed->value,
                ])
                    ->whereDate('starts_at', '<=', $endsAt->toDateString())
                    ->whereDate('ends_at', '>=', $startsAt->toDateString());
            })
            ->get()
            ->toArray();
    }
}
