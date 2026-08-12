<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Client;

class OauthClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spectacular:oauth-clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete unused dynamically registered OAuth clients.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $where_live = fn(Builder $query) => $query
            ->where('revoked', false)
            ->where(fn(Builder $query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now()));

        $deleted = Client::query()
            ->whereNull('owner_id')
            ->whereJsonContains('grant_types', 'authorization_code')
            ->where('created_at', '<', now()->subDay())
            ->whereDoesntHave('tokens', $where_live)
            ->whereDoesntHave('tokens.refreshToken', $where_live)
            ->whereDoesntHave('authCodes', $where_live)
            ->delete();

        $this->components->info('Purged ' . $deleted . ' dynamically registered OAuth clients.');

        return self::SUCCESS;
    }
}
