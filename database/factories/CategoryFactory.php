<?php

namespace Database\Factories;

use App\Enums\Bucket;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->word(),
            'bucket' => Bucket::Living,
            'is_income' => false,
        ];
    }

    /** A system category that belongs to nobody. */
    public function system(): static
    {
        return $this->state(['user_id' => null]);
    }
}
