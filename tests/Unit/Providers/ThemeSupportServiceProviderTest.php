<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Providers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Config;
use themes\Wordpress\Framework\Core\Providers\ThemeSupportServiceProvider;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Functions;

class ThemeSupportServiceProviderTest extends WordpressTestCase
{
    /** @test */
    public function should_call_add_theme_support_for_key_in_config()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config;

        $config->set('app.themeSupport', [
            'post-thumbnail',
        ]);

        Functions\expect('add_theme_support')
            ->with('post-thumbnail')
            ->once();

        $provider = new ThemeSupportServiceProvider($app);
        $provider->boot($config);
    }

    /** @test */
    public function should_call_add_theme_support_for_key_value_in_config()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config;

        $config->set('app.themeSupport', [
            'post-formats' => ['aside', 'gallery'],
        ]);

        Functions\expect('add_theme_support')
            ->with('post-formats', ['aside', 'gallery'])
            ->once();

        $provider = new ThemeSupportServiceProvider($app);
        $provider->boot($config);
    }
}
