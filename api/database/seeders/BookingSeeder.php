<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();

        if ($user === null) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $rooms = Room::query()->orderBy('id')->get();

        if ($rooms->count() < 5) {
            return;
        }

        $bookings = [
            [
                'room_id' => $rooms[0]->id,
                'starts_at' => '2026-06-01',
                'ends_at' => '2026-06-03',
                'participants_count' => 2,
                'status' => BookingStatus::Pending,
            ],
            [
                'room_id' => $rooms[1]->id,
                'starts_at' => '2026-06-04',
                'ends_at' => '2026-06-06',
                'participants_count' => 4,
                'status' => BookingStatus::Pending,
            ],
            [
                'room_id' => $rooms[2]->id,
                'starts_at' => '2026-06-07',
                'ends_at' => '2026-06-08',
                'participants_count' => 3,
                'status' => BookingStatus::Pending,
            ],
            [
                'room_id' => $rooms[3]->id,
                'starts_at' => '2026-06-09',
                'ends_at' => '2026-06-11',
                'participants_count' => 2,
                'status' => BookingStatus::Confirmed,
            ],
            [
                'room_id' => $rooms[4]->id,
                'starts_at' => '2026-06-12',
                'ends_at' => '2026-06-14',
                'participants_count' => 6,
                'status' => BookingStatus::Confirmed,
            ],
            [
                'room_id' => $rooms[0]->id,
                'starts_at' => '2026-06-15',
                'ends_at' => '2026-06-16',
                'participants_count' => 2,
                'status' => BookingStatus::Confirmed,
            ],
            [
                'room_id' => $rooms[1]->id,
                'starts_at' => '2026-06-17',
                'ends_at' => '2026-06-19',
                'participants_count' => 4,
                'status' => BookingStatus::Cancelled,
            ],
            [
                'room_id' => $rooms[2]->id,
                'starts_at' => '2026-06-20',
                'ends_at' => '2026-06-22',
                'participants_count' => 3,
                'status' => BookingStatus::Cancelled,
            ],
            [
                'room_id' => $rooms[3]->id,
                'starts_at' => '2026-06-23',
                'ends_at' => '2026-06-24',
                'participants_count' => 2,
                'status' => BookingStatus::Cancelled,
            ],
        ];

        foreach ($bookings as $booking) {
            Booking::factory()
                ->for(Room::query()->findOrFail($booking['room_id']))
                ->for($user)
                ->create([
                    'starts_at' => $booking['starts_at'],
                    'ends_at' => $booking['ends_at'],
                    'participants_count' => $booking['participants_count'],
                    'status' => $booking['status'],
                ]);
        }
    }
}
