<?php

namespace Dcblogdev\Xero\database\factories;

use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Database\Eloquent\Factories\Factory;

class TokenFactory extends Factory
{
    protected $model = XeroToken::class;

    public function definition(): array
    {
        return [
            'tenant_id' => $this->faker->uuid,
            'tenant_name' => $this->faker->name,
            'access_token' => $this->faker->uuid,
            'refresh_token' => $this->faker->uuid,
            'expires_in' => $this->faker->randomNumber(),
            'created_at' => $this->faker->dateTime,
            'updated_at' => $this->faker->dateTime,
            'scopes' => config('xero.scopes'),
        ];
    }
}
