<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\EloquentRoomRepository;
use App\Repositories\RoomRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    public $singletons = [
        RoomRepository::class => EloquentRoomRepository::class,
    ];
}
