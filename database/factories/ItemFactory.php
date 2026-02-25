<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'item_code' => 'SKU-'.fake()->unique()->numberBetween(1000, 99999),
            'category_id' => Category::factory(),
            'sub_category' => null,
            'brand' => null,
            'model_no' => null,
            'description' => null,
            'item_type' => 'goods',
            'status' => 'active',
            'item_image' => null,
        ];
    }

    /**
     * Configure the factory: create related tax, pricing, inventory, compliance.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Item $item): void {
            $item->taxDetail()->create([]);
            $item->pricing()->create([]);
            $item->inventory()->create(['primary_unit' => 'Nos']);
            $item->compliance()->create([]);
        });
    }
}
