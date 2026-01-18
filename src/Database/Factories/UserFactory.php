<?php

namespace themes\Wordpress\Framework\Core\Database\Factories;

use themes\Wordpress\Framework\Core\Database\Concerns\CurrentDate;
use Illuminate\Support\Str;
use Timber\User;

class UserFactory extends Factory
{
    use CurrentDate;

    /**
     * Define the user default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_pass'       => wp_hash_password(Str::random()),
            'user_login'      => $this->faker->unique()->userName(),
            'user_url'        => $this->faker->url(),
            'user_email'      => $this->faker->unique()->email(),
            'display_name'    => $this->faker->name(),
            'first_name'      => $this->faker->firstName(),
            'last_name'       => $this->faker->lastName(),
            'post_excerpt'    => $this->faker->realText(),
            'user_registered' => $this->now(),
        ];
    }

    /**
     * Generates entry of user.
     *
     * @return User|null
     */
    public function generate(): ?User
    {
        $user = wp_insert_user($this->getRawAttributes());

        if (is_wp_error($user)) {
            return null;
        }

        add_user_meta($user, '_wp-framework-core-fake', true);

        if (isset($this->getRawAttributes()['user_meta']) && is_array($this->getRawAttributes()['user_meta'])) {
            foreach ($this->getRawAttributes()['user_meta'] as $key => $value) {
                add_user_meta($user, $key, $value);
            }
        }

        return new User($user);
    }
}
