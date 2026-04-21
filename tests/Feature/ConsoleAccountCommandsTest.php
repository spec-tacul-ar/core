<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConsoleAccountCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_create_command_creates_an_account_with_a_provided_password(): void
    {
        $this->artisan('spectacular:account:create', [
            'email' => 'created@example.test',
            'name' => 'Created Account',
        ])
            ->expectsQuestion('Password (leave blank for random password)', 'provided-password')
            ->expectsOutput('User created.')
            ->assertSuccessful();

        $account = Account::query()->where('email', 'created@example.test')->firstOrFail();

        $this->assertSame('Created Account', $account->name);
        $this->assertTrue(Hash::check('provided-password', $account->password));
    }

    public function test_account_create_command_rejects_duplicate_email_addresses(): void
    {
        Account::factory()->create(['email' => 'taken@example.test']);

        $this->artisan('spectacular:account:create', [
            'email' => 'taken@example.test',
            'name' => 'Duplicate Account',
        ])
            ->expectsOutput('That email address is already in use.')
            ->assertExitCode(Command::INVALID);
    }

    public function test_account_create_command_requires_an_email_address(): void
    {
        $this->artisan('spectacular:account:create')
            ->expectsQuestion('Email address', '')
            ->expectsOutput('An email address is required.')
            ->assertExitCode(Command::INVALID);
    }

    public function test_account_create_command_attaches_existing_solo_projects_to_the_first_account(): void
    {
        $projects = Project::factory()->count(2)->create();

        $this->artisan('spectacular:account:create', [
            'email' => 'first@example.test',
            'name' => 'First Account',
        ])
            ->expectsQuestion('Password (leave blank for random password)', 'provided-password')
            ->expectsOutput('User created.')
            ->assertSuccessful();

        $account = Account::query()->where('email', 'first@example.test')->firstOrFail();

        foreach ($projects as $project) {
            $this->assertDatabaseHas('contributors', [
                'account_id' => $account->id,
                'project_id' => $project->id,
                'role' => Role::OWNER->value,
            ]);
        }
    }

    public function test_account_password_command_resets_an_existing_account_password(): void
    {
        $account = Account::factory()->create([
            'email' => 'reset@example.test',
            'password' => Hash::make('old-password'),
        ]);

        $this->artisan('spectacular:account:password', [
            'email' => $account->email,
        ])
            ->expectsQuestion('New password (leave blank for random password)', 'new-password')
            ->expectsOutput('Password reset.')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('new-password', $account->fresh()->password));
    }

    public function test_account_password_command_reports_missing_accounts(): void
    {
        $this->artisan('spectacular:account:password', [
            'email' => 'missing@example.test',
        ])
            ->expectsOutput('No account found for that email address.')
            ->assertExitCode(Command::INVALID);
    }

    public function test_account_password_command_requires_an_email_address(): void
    {
        $this->artisan('spectacular:account:password')
            ->expectsQuestion('Email address', '')
            ->expectsOutput('An email address is required.')
            ->assertExitCode(Command::INVALID);
    }
}
