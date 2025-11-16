<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Log;

class TestGroundSlots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ground-slots {date} {ground_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the ground slots functionality';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = $this->argument('date');
        $groundId = $this->argument('ground_id');

        $this->info("Testing ground slots for date: {$date}, ground ID: {$groundId}");

        try {
            $controller = app(UserController::class);
            $response = $controller->getGroundSlots($date, $groundId);

            $data = json_decode($response->getContent(), true);

            $this->info("Response: " . json_encode($data, JSON_PRETTY_PRINT));

            if ($data['success']) {
                $this->info("Number of slots: " . count($data['slots']));
                foreach ($data['slots'] as $index => $slot) {
                    $this->line("Slot {$index}: {$slot['time']} - Available: " . ($slot['available'] ? 'Yes' : 'No'));
                }
            } else {
                $this->error("Error: " . ($data['message'] ?? 'Unknown error'));
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
