<?php

declare(strict_types=1);

namespace App\Repositories;

use DateTimeInterface;
use Illuminate\Support\Collection;

interface RoomRepository
{
    public function getAvailable(?DateTimeInterface $startsAt = null, ?DateTimeInterface $endsAt = null): Collection;
}
