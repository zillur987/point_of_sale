<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class BranchApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // If your routes require a permission, grant it to this user here.
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_it_lists_branches_with_filters(): void
    {
        Branch::factory()->create(['name' => 'Dhaka Main', 'code' => 'DHK']);
        Branch::factory()->inactive()->create(['name' => 'Old Branch']);

        $this->getJson('/api/v1/branches?search=Dhaka')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/v1/branches?is_active=0')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_it_creates_a_branch_and_uppercases_code(): void
    {
        $this->postJson('/api/v1/branches', ['name' => 'Gulshan', 'code' => 'gls'])
            ->assertCreated()
            ->assertJsonPath('data.code', 'GLS');

        $this->assertDatabaseHas('branches', ['code' => 'GLS']);
    }

    public function test_it_validates_unique_code(): void
    {
        Branch::factory()->create(['code' => 'DHK']);

        $this->postJson('/api/v1/branches', ['name' => 'X', 'code' => 'DHK'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');
    }

    public function test_it_updates_a_branch(): void
    {
        $branch = Branch::factory()->create();

        $this->putJson("/api/v1/branches/{$branch->id}", ['name' => 'Renamed', 'code' => $branch->code])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');
    }

    public function test_it_soft_deletes_a_branch(): void
    {
        $branch = Branch::factory()->create();

        $this->deleteJson("/api/v1/branches/{$branch->id}")->assertNoContent();

        $this->assertSoftDeleted('branches', ['id' => $branch->id]);
    }
}
