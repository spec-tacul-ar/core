<?php

namespace App\Console\Commands;

use App\Actions\Fortify\CreateNewAccount;
use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spectacular:account:create
                            {email? : The email address of the user}
                            {name? : The name of the user}
                            {--unverified : Do not mark the email address as verified}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user account.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('Email address');

        if (!$email) {
            $this->error('An email address is required.');

            return self::INVALID;
        }

        if (Account::where('email', $email)->exists()) {
            $this->error('That email address is already in use.');

            return self::INVALID;
        }

        if (!$name = $this->argument('name')) {
            $name = Str::of($email)->before('@')->replace('.', ' ')->headline();

            $name = $this->ask('Name', $name);
        }

        $provided_password = $this->secret('Password (leave blank for random password)');

        $password = $provided_password ?: Str::password(16);

        try {
            $account = new CreateNewAccount()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
        } catch (ValidationException $exception) {
            $this->error($exception->getMessage());

            return self::INVALID;
        }

        if (!$this->option('unverified')) {
            $account->markEmailAsVerified();
        }

        if (!$provided_password) {
            $this->info('User created with password: ' . $password);
        } else {
            $this->info('User created.');
        }

        return self::SUCCESS;
    }
}
