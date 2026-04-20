<?php

namespace App\Console\Commands;

use App\Models\Account;
use Illuminate\Console\Command;

class AccountVerify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spectacular:account:verify
                            {email? : The email address of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify a user account by email address.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask('Email address');

        if (!$email) {
            $this->error('An email address is required.');

            return self::INVALID;
        }

        $account = Account::findByEmail($email);

        if (!$account) {
            $this->error('No account found for that email address.');

            return self::INVALID;
        }

        if ($account->hasVerifiedEmail()) {
            $this->info('Account is already verified.');

            return self::SUCCESS;
        }

        $account->markEmailAsVerified();

        $this->info('Account verified.');

        return self::SUCCESS;
    }
}
