<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class runBothPlayerShipsCommand extends Command
{
    protected $signature = 'run:both-player-stats';
    protected $description = 'Run both player ships and overall stats commands concurrently';

    public function handle()
    {
        Log::info('RunBothPlayerStatsCommand started.');

        // Adjust the PHP binary path if needed on your server.
        $php = 'php';

        // These commands must match your command signatures.
        $commandShips = [$php, 'artisan', 'fetch:player-ships'];
        $commandOverall = [$php, 'artisan', 'fetch:overall-stats'];

        // Create processes
        $processShips = new Process($commandShips);
        $processOverall = new Process($commandOverall);

        // Start both concurrently.
        $processShips->start();
        $processOverall->start();

        // Optionally, wait for both processes to finish.
        $processShips->wait();
        $processOverall->wait();

        // Log output for debugging:
        Log::info('PlayerShips command output:', ['output' => $processShips->getOutput()]);
        Log::info('OverallStats command output:', ['output' => $processOverall->getOutput()]);

        $this->info('Both player stats commands executed concurrently.');
        return 0;
    }
}
