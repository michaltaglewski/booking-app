<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BookingController;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(BookingController::class)]
class BookingControllerTest extends TestCase
{
    private const string API_BOOKINGS_PATH = '/api/bookings';

    use RefreshDatabase;

    private User $user;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->room = Room::factory()->create([
            'name' => 'Amber Room',
            'capacity' => 2
        ]);

        // Mock current date for testing
        $today = Carbon::createFromFormat('Y-m-d', '2026-06-01');
        Carbon::setTestNow($today);
    }

    #[Test]
    public function shouldReturnUnauthorizedWhenUnauthenticatedUserAccessesBookings(): void
    {
        // Given
        Booking::factory()->for($this->room)->for($this->user)->create();

        // When
        $this->actingAsGuest()
            ->getJson(self::API_BOOKINGS_PATH)
            ->assertUnauthorized();
    }

    #[Test]
    public function shouldReturnOnlyAuthenticatedUsersBookingsWithRoomData(): void
    {
        // Given
        $otherUser = User::factory()->create();

        Booking::factory()->for($this->room)->for($this->user)->create([
            'starts_at' => '2026-06-10',
            'ends_at' => '2026-06-12',
        ]);

        Booking::factory()->for($this->room)->for($otherUser)->create();

        // When
        $this->actingAs($this->user)
            ->getJson(self::API_BOOKINGS_PATH)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'room',
                        'starts_at',
                        'ends_at',
                        'participants_count',
                        'status',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    #[Test]
    function shouldCreateBookingForAuthenticatedUser(): void
    {
        // When
        $this->actingAs($this->user)
            ->postJson(self::API_BOOKINGS_PATH, [
                'room_id' => $this->room->id,
                'starts_at' => '2026-06-10',
                'ends_at' => '2026-06-12',
                'participants_count' => 2,
            ])
            ->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'room',
                    'starts_at',
                    'ends_at',
                    'participants_count',
                    'status',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson(['data' => ['status' => 'pending']]);
    }

    #[Test]
    function shouldCreateBookingForAuthenticatedUserWhenRoomIsAvailable(): void
    {
        // Given
        Booking::factory()->for($this->room)->create([ // Same room before the booking date
            'starts_at' => '2026-06-07',
            'ends_at' => '2026-06-09',
        ]);

        Booking::factory()->for($this->room)->create([ // Same room after the booking date
            'starts_at' => '2026-06-13',
            'ends_at' => '2026-06-15',
        ]);

        // When
        $this->actingAs($this->user)
            ->postJson(self::API_BOOKINGS_PATH, [
                'room_id' => $this->room->id,
                'starts_at' => '2026-06-10',
                'ends_at' => '2026-06-12',
                'participants_count' => 2,
            ])
            ->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'room',
                    'starts_at',
                    'ends_at',
                    'participants_count',
                    'status',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson(['data' => ['status' => 'pending']]);
    }

    #[Test]
    function shouldCreateBookingForAuthenticatedUserWhenOverlappingCancelledBookingExists(): void
    {
        // Given
        Booking::factory()->for($this->room)->create([
            'starts_at' => '2026-06-12',
            'ends_at' => '2026-06-12',
            'status' => 'cancelled',
        ]);

        // When
        $this->actingAs($this->user)
            ->postJson(self::API_BOOKINGS_PATH, [
                'room_id' => $this->room->id,
                'starts_at' => '2026-06-10',
                'ends_at' => '2026-06-12',
                'participants_count' => 2,
            ])
            ->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'room',
                    'starts_at',
                    'ends_at',
                    'participants_count',
                    'status',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson(['data' => ['status' => 'pending']]);
    }

    #[Test]
    function shouldNotCreateBookingWhenStartsAtIsInThePast(): void
    {
        // When
        $this->actingAs($this->user)
            ->postJson(self::API_BOOKINGS_PATH, [
                'room_id' => $this->room->id,
                'starts_at' => '2026-05-31',
                'ends_at' => '2026-06-12',
                'participants_count' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['starts_at']);
    }

    #[Test]
    function shouldNotCreateBookingWhenEndsAtIsBeforeStartsAt(): void
    {
        // When
        $this->actingAs($this->user)
            ->postJson(self::API_BOOKINGS_PATH, [
                'room_id' => $this->room->id,
                'starts_at' => '2026-06-10',
                'ends_at' => '2026-06-09',
                'participants_count' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ends_at']);
    }

    #[Test]
    function shouldNotCreateBookingWhenParticipantsCountExceedsRoomCapacity(): void
    {
        // When
        $this->actingAs($this->user)
            ->postJson(self::API_BOOKINGS_PATH, [
                'room_id' => $this->room->id,
                'starts_at' => '2026-06-10',
                'ends_at' => '2026-06-12',
                'participants_count' => 3,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['participants_count']);
    }

    #[Test]
    function shouldNotCreateBookingWhenAnotherBookingOverlaps(): void
    {
        // Given
        Booking::factory()->for($this->room)->create([
            'starts_at' => '2026-06-12',
            'ends_at' => '2026-06-12',
        ]);

        // When
        $this->actingAs($this->user)
            ->postJson(self::API_BOOKINGS_PATH, [
                'room_id' => $this->room->id,
                'starts_at' => '2026-06-10',
                'ends_at' => '2026-06-12',
                'participants_count' => 2,
            ])
            ->assertConflict();
    }

    #[Test]
    function shouldCancelBookingForAuthenticatedUser(): void
    {
        // Given
        $booking = Booking::factory()->for($this->room)->for($this->user)->create();

        // When
        $this->actingAs($this->user)
            ->patchJson(self::API_BOOKINGS_PATH . '/' . $booking->id . '/cancel')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'room',
                    'starts_at',
                    'ends_at',
                    'participants_count',
                    'status',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson(['data' => ['status' => 'cancelled']]);
    }

    #[Test]
    function shouldNotCancelBookingForUnauthenticatedUser(): void
    {
        // Given
        $booking = Booking::factory()->for($this->room)->for($this->user)->create();

        // When
        $this->actingAsGuest()
            ->patchJson(self::API_BOOKINGS_PATH . '/' . $booking->id . '/cancel')
            ->assertUnauthorized();
    }

    #[Test]
    function shouldNotCancelBookingWhenItDoesNotExist(): void
    {
        // When
        $this->actingAs($this->user)
            ->patchJson(self::API_BOOKINGS_PATH . '/9544bb0b-9a2c-3928-8624-419fb7adec2d/cancel')
            ->assertNotFound();
    }

    #[Test]
    function shouldNotCancelBookingWhenUserDoesNotHavePermissionToThisResource(): void
    {
        // Given
        $otherUser = User::factory()->create();
        $booking = Booking::factory()->for($this->room)->for($otherUser)->create();

        // When
        $this->actingAs($this->user)
            ->patchJson(self::API_BOOKINGS_PATH . '/' . $booking->id . '/cancel')
            ->assertForbidden();
    }

    #[Test]
    function shouldNotCancelBookingWhenItIsAlreadyCancelled(): void
    {
        // Given
        $booking = Booking::factory()->for($this->room)->for($this->user)->create([
            'status' => 'cancelled'
        ]);

        // When
        $this->actingAs($this->user)
            ->patchJson(self::API_BOOKINGS_PATH . '/' . $booking->id . '/cancel')
            ->assertBadRequest();
    }
}
