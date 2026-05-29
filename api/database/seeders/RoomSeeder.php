<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['name' => 'Amber Room', 'capacity' => 2],
            ['name' => 'Blue Room', 'capacity' => 4],
            ['name' => 'Green Room', 'capacity' => 6],
            ['name' => 'Silver Suite', 'capacity' => 3],
            ['name' => 'Executive Loft', 'capacity' => 8],
        ];

        foreach ($rooms as $room) {
            Room::factory()->create($room);
        }
    }
}
