<?php

namespace themes\Wordpress\Framework\Core\Test\Integration;

use themes\Wordpress\Framework\Core\Database\Factories\UserFactory;
use Timber\User;

class UserFactoryTest extends IntegrationTestCase
{
    public function test_it_can_create_an_user()
    {
        $user = UserFactory::new()->make([
            'user_meta' => [
                'foo'  => 'bar',
                'john' => 'doe',
            ],
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->user_login);
        $this->assertNotEmpty($user->user_url);
        $this->assertNotEmpty($user->user_email);
        $this->assertNotEmpty($user->display_name);
        $this->assertNotEmpty($user->first_name);
        $this->assertNotEmpty($user->last_name);
        $this->assertNotEmpty($user->user_registered);
        $this->assertIsArray($user->roles());
        $this->assertEquals([
            'subscriber' => 'Subscriber',
        ], $user->roles());
        $this->assertTrue((bool) $user->get_meta_field('_wp-framework-core-fake'));
        $this->assertEquals('bar', $user->get_meta_field('foo'));
        $this->assertEquals('doe', $user->get_meta_field('john'));
    }
}
