<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'username' => fake()->unique()->userName(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Configure the model factory to create an admin.
     * Note: You need to attach the role manually after creating the user:
     * $user = User::factory()->admin()->create();
     * $user->roles()->attach(Role::where('slug', 'admin')->first());
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => []);
    }

    /**
     * Configure the model factory to create a commercial user.
     * Note: You need to attach the role manually after creating the user:
     * $user = User::factory()->commercial()->create();
     * $user->roles()->attach(Role::where('slug', 'commercial')->first());
     */
    public function commercial(): static
    {
        return $this->state(fn (array $attributes) => []);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
