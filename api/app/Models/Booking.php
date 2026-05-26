<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $room_id
 * @property int $user_id
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property BookingStatus $status pending | confirmed | cancelled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Booking extends Model
{
    protected $fillable = ['room_id', 'user_id', 'starts_at', 'ends_at', 'participants_count', 'status'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'status' => BookingStatus::class
    ];
}
