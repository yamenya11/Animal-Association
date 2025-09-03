<?php

namespace App\Console;
use App\Models\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\VaccineService;
use App\Models\User;
use App\Notifications\VaccineDueNotification;
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
protected function schedule(Schedule $schedule): void
{
    $schedule->call(function () {
        try {
            $vaccines = app(\App\Services\VaccineService::class)->dueToday();

            foreach ($vaccines as $vaccine) {
                $users = \App\Models\User::role('vet')->get();

                foreach ($users as $user) {
                    $user->notify(new \App\Notifications\VaccineDueNotification($vaccine));
                }
            }
        } catch (\Throwable $e) {
            \Log::error('❌ Vaccine schedule error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    })->everyMinute();
}



    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }


}
