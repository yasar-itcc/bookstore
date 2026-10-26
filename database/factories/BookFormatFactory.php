<?php

namespace Database\Factories;

use App\Enums\BookFormat as Format;
use App\Models\Book;
use App\Models\BookFormat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookFormat>
 */
class BookFormatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mrp = fake()->randomElement([199, 249, 299, 399, 499, 599]);

        return [
            'book_id' => Book::factory(),
            'format' => Format::Paperback,
            'mrp' => $mrp,
            'price' => $mrp - fake()->randomElement([0, 20, 50]),
            'stock' => fake()->numberBetween(0, 40),
            'gst_rate' => 0,
            'is_active' => true,
        ];
    }

    public function ebook(): static
    {
        return $this->state(fn () => [
            'format' => Format::Ebook,
            'stock' => null,
            'gst_rate' => 18,
        ]);
    }

    public function stock(int $stock): static
    {
        return $this->state(fn () => ['stock' => $stock]);
    }
}
