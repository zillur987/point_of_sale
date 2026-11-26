<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Grant your catalog permission here if your routes require one.
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_it_creates_a_category_with_a_unique_slug(): void
    {
        $this->postJson('/api/v1/categories', ['name' => 'Beverages'])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'beverages');

        $this->postJson('/api/v1/categories', ['name' => 'Beverages'])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'beverages-2');
    }

    public function test_it_returns_a_nested_tree(): void
    {
        $root = Category::factory()->create(['name' => 'Food']);
        Category::factory()->create(['name' => 'Snacks', 'parent_id' => $root->id]);

        $this->getJson('/api/v1/categories/tree')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.children.0.name', 'Snacks');
    }

    public function test_it_blocks_moving_a_category_under_its_own_descendant(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->putJson("/api/v1/categories/{$parent->id}", ['name' => $parent->name, 'parent_id' => $child->id])
            ->assertUnprocessable();
    }

    public function test_it_blocks_deleting_a_category_used_by_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $this->deleteJson("/api/v1/categories/{$category->id}")->assertUnprocessable();
        $this->assertNotSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_it_deletes_an_unused_category(): void
    {
        $category = Category::factory()->create();

        $this->deleteJson("/api/v1/categories/{$category->id}")->assertNoContent();
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
