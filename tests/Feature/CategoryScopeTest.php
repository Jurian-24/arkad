<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Factories\CategoryFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_sees_their_own_and_the_system_categories(): void
    {
        $user = User::factory()->create();

        $own = CategoryFactory::new()->create(['user_id' => $user->id, 'name' => 'Own']);
        $system = CategoryFactory::new()->system()->create(['name' => 'System']);

        $names = Category::availableTo($user)->pluck('name');

        $this->assertTrue($names->contains('Own'));
        $this->assertTrue($names->contains('System'));
    }

    public function test_a_user_does_not_see_the_categories_of_someone_else(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        CategoryFactory::new()->create(['user_id' => $other->id, 'name' => 'Secret']);

        $this->assertFalse(Category::availableTo($user)->pluck('name')->contains('Secret'));
    }

    public function test_a_subcategory_knows_its_parent_and_the_parent_its_children(): void
    {
        $user = User::factory()->create();
        $parent = CategoryFactory::new()->create(['user_id' => $user->id, 'name' => 'Wonen']);
        $child = CategoryFactory::new()->create(['user_id' => $user->id, 'name' => 'Huur', 'parent_id' => $parent->id]);

        $this->assertSame($parent->id, $child->parent->id);
        $this->assertSame([$child->id], $parent->children->pluck('id')->all());
    }

    public function test_deleting_a_parent_turns_its_children_into_top_level_categories(): void
    {
        $user = User::factory()->create();
        $parent = CategoryFactory::new()->create(['user_id' => $user->id]);
        $child = CategoryFactory::new()->create(['user_id' => $user->id, 'parent_id' => $parent->id]);

        $parent->delete();

        $this->assertNotNull(Category::find($child->id));
        $this->assertNull($child->fresh()->parent_id);
    }
}
