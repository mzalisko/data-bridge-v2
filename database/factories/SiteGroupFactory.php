<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SiteGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => fake()->words(2, true),
            'description' => null,
            'color'       => '#5288c1',
        ];
    }
}
