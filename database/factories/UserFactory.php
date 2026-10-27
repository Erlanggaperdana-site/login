<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Pembuat data user palsu untuk pengujian dan pengisian data contoh.
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /** Password yang dipakai bersama oleh semua user hasil factory. */
    protected static ?string $password;

    /** Nilai bawaan untuk satu user baru: nama, email unik, dan role customer. */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
            'remember_token' => Str::random(10),
        ];
    }

    /** Membuat user dengan role admin. Contoh: User::factory()->admin()->create(). */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
        ]);
    }

    /** Membuat user yang email-nya belum diverifikasi. */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
