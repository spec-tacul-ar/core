<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\ClientRepository;
use RuntimeException;

class Oauth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spectacular:oauth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure OAuth keys and a personal access client exist.';

    /**
     * Execute the console command.
     */
    public function handle(ClientRepository $clients): int
    {
        $result = $this->callSilent('passport:keys');

        if ($result === Command::SUCCESS) {
            $this->info('OAuth encryption keys created.');
        } else {
            $this->warn('OAuth encryption keys not created. They might already exist.');
        }

        try {
            $clients->personalAccessClient(config('auth.guards.api.provider'));

            $this->info('OAuth personal access client already exists.');
        } catch (RuntimeException) {
            $result = $this->callSilent('passport:client', [
                '--personal' => true,
                '--name' => config('app.name'),
                '--provider' => config('auth.guards.api.provider'),
            ]);

            if ($result === Command::SUCCESS) {
                $this->info('OAuth personal access client created.');
            } else {
                $this->error('OAuth personal access client not created.');
            }
        }

        return Command::SUCCESS;
    }
}
