<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ClearRoomsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-rooms {--period= : Clear cache for specific period (format: Y-m-d,Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear rooms availability cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('period')) {
            $this->clearSpecificPeriod();
        } else {
            $this->clearAllRoomsCache();
        }

        $this->info('Rooms cache cleared successfully!');
    }

    /**
     * Clear cache for specific period
     */
    private function clearSpecificPeriod()
    {
        $period = $this->option('period');
        $dates = explode(',', $period);

        if (count($dates) !== 2) {
            $this->error('Period format should be: Y-m-d,Y-m-d (e.g., 2024-01-01,2024-01-07)');
            return;
        }

        try {
            $startDate = Carbon::parse($dates[0]);
            $endDate = Carbon::parse($dates[1]);
            
            $cacheKey = "available_rooms_{$startDate->toDateString()}_{$endDate->toDateString()}";
            Cache::forget($cacheKey);
            
            $this->info("Cleared cache for period: {$startDate->toDateString()} - {$endDate->toDateString()}");
        } catch (\Exception $e) {
            $this->error('Invalid date format. Use Y-m-d format (e.g., 2024-01-01)');
        }
    }

    /**
     * Clear all rooms cache
     */
    private function clearAllRoomsCache()
    {        for ($i = 0; $i < 30; $i++) {
            $startDate = Carbon::today()->addDays($i);
            $endDate = $startDate->copy()->addDays(7);
            
            $cacheKey = "available_rooms_{$startDate->toDateString()}_{$endDate->toDateString()}";
            Cache::forget($cacheKey);
        }

        $this->info('Cleared rooms cache for the last 30 days');
    }
}
