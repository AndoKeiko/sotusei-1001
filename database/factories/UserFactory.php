<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'nickname' => $this->faker->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // デフォルトパスワード
            'remember_token' => Str::random(10),
            'google_id' => $this->faker->optional()->uuid(),
            'avatar' => $this->faker->optional()->imageUrl(200, 200, 'people'),
        ];
    }

    /**
     * メール未認証のユーザーの状態を示す
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}