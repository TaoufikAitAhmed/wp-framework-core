<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Providers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Bootstrappers\RegisterProviders;
use themes\Wordpress\Framework\Core\Config;
use themes\Wordpress\Framework\Core\Http\Lumberjack;
use themes\Wordpress\Framework\Core\Providers\TimberServiceProvider;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Functions;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Timber\Timber;

/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */
class TimberServiceProviderTest extends WordpressTestCase
{
    /** @test */
    public function timber_plugin_is_initialiased()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application(__DIR__ . '/../');
        $lumberjack = new Lumberjack($app);

        $app->register(new TimberServiceProvider($app));
        $lumberjack->bootstrap();

        $this->assertTrue($app->has('timber'));
        $this->assertSame($app->get('timber'), $app->get(Timber::class));
    }

    /** @test */
    public function dirname_variable_is_set_from_config()
    {
        $app = new Application(__DIR__ . '/../');

        $config = new Config;
        $config->set('timber.paths', [
            'path/one',
            'path/two',
            'path/three',
        ]);

        $app->bind('config', $config);
        $app->bind(Config::class, $config);

        $app->bootstrapWith([
            RegisterProviders::class,
            BootProviders::class,
        ]);

        $app->register(new TimberServiceProvider($app));

        $this->assertTrue($app->has('timber'));
        $this->assertSame([
            'path/one',
            'path/two',
            'path/three',
        ], $app->get('timber')::$dirname);
    }
}
