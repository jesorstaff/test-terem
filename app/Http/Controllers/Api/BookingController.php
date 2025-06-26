<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $room = Room::find($validated['room_id']);
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);

        $overlap = $room->bookings()
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<', $end)
                  ->where('end_date', '>', $start);
            })->exists();

        if ($overlap) {
            return response()->json(['message' => 'Номер недоступен на выбранные даты'], 422);
        }

        $booking = Booking::create([
            'room_id' => $room->id,
            'user_id' => Auth::id(),
            'start_date' => $start,
            'end_date' => $end,
        ]);

        $this->clearRoomsCache($start, $end);

        Log::info('Booking confirmation email', [
            'user' => Auth::user()->email,
            'room' => $room->name,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);

        return response()->json(['message' => 'Номер успешно забронирован'], 201);
    }

    private function clearRoomsCache($start, $end)
    {
        $cacheKey = "available_rooms_{$start->toDateString()}_{$end->toDateString()}";
        Cache::forget($cacheKey);

        $defaultStart = Carbon::today();
        $defaultEnd = Carbon::today()->addDays(7);
        $defaultCacheKey = "available_rooms_{$defaultStart->toDateString()}_{$defaultEnd->toDateString()}";
        Cache::forget($defaultCacheKey);
    }
}
