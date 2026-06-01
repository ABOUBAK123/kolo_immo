<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'               => fake()->name(),
            'email'              => fake()->unique()->safeEmail(),
            'phone'              => '+225' . fake()->numerify('07########'),
            'email_verified_at'  => now(),
            'phone_verified_at'  => now(),
            'password'           => static::$password ??= Hash::make('password'),
            'remember_token'     => Str::random(10),
            'role'               => 'tenant',
            'kyc_status'         => 'approved',
            'is_active'          => true,
            'is_banned'          => false,
            'trust_score'        => 80,
            'country'            => fake()->randomElement(["Côte d'Ivoire", 'Sénégal', 'Mali']),
            'city'               => fake()->randomElement(['Abidjan', 'Dakar', 'Bamako']),
            'language'           => 'fr',
            'currency'           => 'XOF',
        ];
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null, 'phone_verified_at' => null]);
    }

    public function owner(): static
    {
        return $this->state(['role' => 'owner', 'kyc_status' => 'approved']);
    }

    public function tenant(): static
    {
        return $this->state(['role' => 'tenant']);
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin', 'is_active' => true]);
    }

    public function banned(): static
    {
        return $this->state(['is_banned' => true, 'is_active' => false]);
    }

    public function kycPending(): static
    {
        return $this->state(['kyc_status' => 'pending']);
    }
}
