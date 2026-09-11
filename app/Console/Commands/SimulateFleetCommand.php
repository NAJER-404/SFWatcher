<?php

namespace App\Console\Commands;

use App\Services\FleetSimulatorService;
use Illuminate\Console\Command;

class SimulateFleetCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'transit:simulate-fleet 
                            {--interval=3 : Seconds between telemetry broadcasts}
                            {--ticks=20 : Total ticks to run (0 for infinite)}';

    /**
     * The console command description.
     */
    protected $description = 'Simulate simultaneous real-time GPS telemetry broadcasts for all 5 Agusan del Sur fleet corridors';

    /**
     * Execute the console command.
     */
    public function handle(FleetSimulatorService $simulator): int
    {
        $interval = (int) $this->option('interval');
        $ticks    = (int) $this->option('ticks');

        $this->info("🚀 Starting Multi-Bus Fleet Telemetry Simulation (Agusan del Sur)");
        $this->info("Corridors: ADS-101 (Patin-ay), ADS-102 (Bayugan), ADS-103 (Trento), ADS-104 (Talacogon), ADS-105 (Town Loop)");
        $this->info("Broadcast Interval: {$interval}s | Total Ticks: " . ($ticks > 0 ? $ticks : 'Infinite'));
        $this->newLine();

        $count = 0;
        while (true) {
            $count++;
            $results = $simulator->stepFleet();

            $this->line("<comment>[Tick #{$count} " . now()->toTimeString() . "]</comment> Broadcasted " . count($results) . " GPS telemetry packets:");
            foreach ($results as $b) {
                $this->line("  🚌 <info>{$b['bus_number']}</info> ({$b['route_name']}) → <comment>{$b['waypoint_name']}</comment> [{$b['latitude']}, {$b['longitude']}] {$b['speed_kmh']} km/h");
            }
            $this->newLine();

            if ($ticks > 0 && $count >= $ticks) {
                $this->info("✓ Simulation completed after {$ticks} ticks.");
                break;
            }

            sleep($interval);
        }

        return Command::SUCCESS;
    }
}
