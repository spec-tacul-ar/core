<?php

namespace App\Console\Commands;

use App\Actions\Fortify\CreateNewAccount;
use App\Enums\Role;
use App\Models\Account;
use App\Models\Project;
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
                            {name? : The name of the user}';

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

        $password = $provided_password ?: Str::random(8);

        try {
            new CreateNewAccount()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
        } catch (ValidationException $exception) {
            $this->error($exception->getMessage());

            return self::INVALID;
        }

        if (!$provided_password) {
            $this->info('User created with password: ' . $password);
        } else {
            $this->info('User created.');
        }

        // If this is the first account, attach any solo projects to them.
        if (Account::count() === 1) {
            $account = Account::first();

            Project::each(fn ($project) => $project->addContributor($account, Role::OWNER));
        }

        return self::SUCCESS;
    }
}
