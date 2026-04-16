<?php

namespace App\Console\Commands;

use App\Actions\Fortify\ResetAccountPassword;
use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spectacular:account:password
                            {email? : The email address of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset a user password by email address.';

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

        $providedPassword = $this->secret('New password (leave blank for random password)');
        $password = $providedPassword ?: Str::password(16);

        try {
            new ResetAccountPassword()->reset($account, [
                'password' => $password,
            ]);
        } catch (ValidationException $exception) {
            $this->error($exception->getMessage());

            return self::INVALID;
        }

        if ($providedPassword) {
            $this->info('Password reset.');
        } else {
            $this->info('Password reset to: ' . $password);
        }

        return self::SUCCESS;
    }
}
