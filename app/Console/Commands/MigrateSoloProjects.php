<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Project;
use Illuminate\Console\Command;

class MigrateSoloProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spectacular:solo:migrate
                            {email? : The email address of the account that should own solo projects}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign legacy solo projects to an existing account.';

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

        Project::query()
            ->doesntHave('collaborations')
            ->get()
            ->each(fn(Project $project) => $project->addCollaboration($account, Role::OWNER));

        return self::SUCCESS;
    }
}
