<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->unique()->sentence(3), '.'),
            'isbn' => fake()->unique()->isbn13(),
            'description' => fake()->paragraphs(2, true),
            'category_id' => Category::factory(),
            'pages' => fake()->numberBetween(80, 800),
            'published_year' => fake()->numberBetween(1990, 2026),
            'is_featured' => false,
            'is_active' => true,
        ];
    }
}
