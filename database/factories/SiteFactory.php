<?php

namespace Database\Factories;

use App\Models\SiteGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group_id'    => SiteGroup::factory(),
            'name'        => fake()->company(),
            'url'         => 'https://' . fake()->domainName(),
            'description' => null,
            'logo'        => null,
            'is_active'   => true,
        ];
    }
}
