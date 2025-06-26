<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = Room::all();
        $users = User::all();

        if ($rooms->isEmpty() || $users->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            $room = $rooms->random();
            $user = $users->random();
            
            
            $startDate = Carbon::now()->addDays(rand(0, 30));
            $endDate = $startDate->copy()->addDays(rand(1, 7));
            
            
            $overlap = Booking::where('room_id', $room->id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where('start_date', '<', $endDate)
                          ->where('end_date', '>', $startDate);
                })->exists();
            
           
            if (!$overlap) {
                Booking::create([
                    'room_id' => $room->id,
                    'user_id' => $user->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);
            }
        }
    }
} 