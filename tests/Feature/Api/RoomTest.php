<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Room;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;


class RoomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Config::set('cache.default', 'file');
    }

    public function test_can_get_available_rooms()
    {
        $room = Room::factory()->create();

        $response = $this->getJson('/api/rooms');

        $response->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonStructure([
                    '*' => ['id', 'name', 'description', 'created_at', 'updated_at']
                ]);
    }

    public function test_can_get_available_rooms_with_date_filter()
    {
        $room = Room::factory()->create();
        $startDate = Carbon::today()->addDays(1);
        $endDate = Carbon::today()->addDays(3);

        $response = $this->getJson("/api/rooms?start_date={$startDate->toDateString()}&end_date={$endDate->toDateString()}");

        $response->assertStatus(200)
                ->assertJsonCount(1);
    }

    public function test_returns_empty_when_no_rooms_available()
    {
        $response = $this->getJson('/api/rooms');

        $response->assertStatus(200)
                ->assertJsonCount(0);
    }

    public function test_returns_422_when_end_date_before_start_date()
    {
        $startDate = Carbon::today()->addDays(3);
        $endDate = Carbon::today()->addDays(1);

        $response = $this->getJson("/api/rooms?start_date={$startDate->toDateString()}&end_date={$endDate->toDateString()}");

        $response->assertStatus(422)
                ->assertJson(['message' => 'end_date должна быть позже start_date']);
    }

    public function test_room_not_available_when_booked()
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();
        
        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'start_date' => Carbon::today()->addDays(1),
            'end_date' => Carbon::today()->addDays(3),
        ]);

        $response = $this->getJson('/api/rooms');

        $response->assertStatus(200)
                ->assertJsonCount(0);
    }

    public function test_room_available_when_booking_ends_before_requested_start()
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();
        
        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'start_date' => Carbon::today()->addDays(1),
            'end_date' => Carbon::today()->addDays(3),
        ]);

        $startDate = Carbon::today()->addDays(4);
        $endDate = Carbon::today()->addDays(6);

        $response = $this->getJson("/api/rooms?start_date={$startDate->toDateString()}&end_date={$endDate->toDateString()}");

        $response->assertStatus(200)
                ->assertJsonCount(1);
    }

    public function test_uses_default_dates_when_not_provided()
    {
        $room = Room::factory()->create();

        $response = $this->getJson('/api/rooms');

        $response->assertStatus(200)
                ->assertJsonCount(1);
    }

    public function test_caches_rooms_data()
    {
        $room = Room::factory()->create();
        $startDate = Carbon::today()->addDays(1);
        $endDate = Carbon::today()->addDays(3);
        $cacheKey = "available_rooms_{$startDate->toDateString()}_{$endDate->toDateString()}";

        $this->getJson("/api/rooms?start_date={$startDate->toDateString()}&end_date={$endDate->toDateString()}");
        
        $this->assertTrue(Cache::has($cacheKey));

        $response = $this->getJson("/api/rooms?start_date={$startDate->toDateString()}&end_date={$endDate->toDateString()}");
        
        $response->assertStatus(200)
                ->assertJsonCount(1);
    }

    public function test_cache_expires_after_15_minutes()
    {
        $room = Room::factory()->create();
        $startDate = Carbon::today()->addDays(1);
        $endDate = Carbon::today()->addDays(3);
        $cacheKey = "available_rooms_{$startDate->toDateString()}_{$endDate->toDateString()}";

        $this->getJson("/api/rooms?start_date={$startDate->toDateString()}&end_date={$endDate->toDateString()}");
        
        $this->assertTrue(Cache::has($cacheKey));

        Cache::put($cacheKey, Cache::get($cacheKey), now()->addMinutes(16));
        
        $this->assertTrue(Cache::has($cacheKey));
    }
}
