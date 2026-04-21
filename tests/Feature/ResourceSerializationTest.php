<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Http\Resources\AccountResource;
use App\Http\Resources\ContributorResource;
use App\Models\Account;
use App\Models\Contributor;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ResourceSerializationTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_resource_only_includes_private_fields_for_the_authenticated_account(): void
    {
        $account = Account::factory()->create(['email' => 'self@example.test']);
        $other = Account::factory()->create(['email' => 'other@example.test']);

        $selfRequest = $this->requestFor($account);
        $otherRequest = $this->requestFor($account);

        $selfData = (new AccountResource($account))->resolve($selfRequest);
        $otherData = (new AccountResource($other))->resolve($otherRequest);

        $this->assertSame('self@example.test', $selfData['email']);
        $this->assertTrue($selfData['is_email_verified']);
        $this->assertArrayNotHasKey('email', $otherData);
        $this->assertArrayNotHasKey('is_email_verified', $otherData);
    }

    public function test_contributor_resource_only_includes_account_name_when_the_account_relation_is_loaded(): void
    {
        $account = Account::factory()->create(['name' => 'Contributor Name']);
        $project = Project::factory()->create();

        $contributor = Contributor::factory()
            ->for($account)
            ->for($project)
            ->create(['role' => Role::VIEWER]);

        $request = $this->requestFor($account);

        $unloadedData = (new ContributorResource($contributor))->resolve($request);
        $loadedData = (new ContributorResource($contributor->fresh()->load('account')))->resolve($request);

        $this->assertArrayNotHasKey('account_name', $unloadedData);
        $this->assertSame('Contributor Name', $loadedData['account_name']);
    }

    private function requestFor(Account $account): Request
    {
        $request = Request::create('/');
        $request->setUserResolver(fn() => $account);

        return $request;
    }
}
