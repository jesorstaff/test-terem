<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $end = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        if ($start && $end && $end < $start) {
            return response()->json(['message' => 'end_date должна быть позже start_date'], 422);
        }

        if (!$start && !$end) {
            $today = Carbon::today();
            $sevenDaysLater = $today->copy()->addDays(7);

            $rooms = Room::where('end_date', '>=', $sevenDaysLater)
                ->whereDoesntHave('bookings', function ($query) use ($today) {
                    $query->where('start_date', '<=', $today)
                          ->where('end_date', '>=', $today);
                })
                ->get();

            return response()->json($rooms);
        }

        if ($start && $end) {
            $rooms = Room::where('start_date', '<', $end)
                ->where('end_date', '>', $start)
                ->whereDoesntHave('bookings', function ($query) use ($start, $end) {
                    $query->where(function ($q) use ($start, $end) {
                        $q->where('start_date', '<', $end)
                          ->where('end_date', '>', $start);
                    });
                })
                ->get();

            return response()->json($rooms);
        }

        return response()->json(['message' => 'Необходимо указать оба параметра: start_date и end_date'], 422);
    }
}
