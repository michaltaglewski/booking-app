<?php

declare(strict_types=1);

namespace App\Repositories;

use DateTimeInterface;

interface RoomRepository
{
    public function getAvailable(?DateTimeInterface $startsAt = null, ?DateTimeInterface $endsAt = null): array;
}
