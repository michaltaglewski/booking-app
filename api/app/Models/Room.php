<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $capacity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Room extends Model
{
    protected $fillable = ['name', 'capacity'];

    protected $casts = [
        'capacity' => 'integer',
    ];
}
