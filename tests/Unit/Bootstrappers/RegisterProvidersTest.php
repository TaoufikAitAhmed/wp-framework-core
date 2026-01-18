<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Bootstrappers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Bootstrappers\RegisterProviders;
use themes\Wordpress\Framework\Core\Config;
use themes\Wordpress\Framework\Core\Providers\ServiceProvider;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Providers\LogServiceProvider;

class RegisterProvidersTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRegistersAllProvidersFoundInConfig()
    {
        $app = new Application(__DIR__ . '/fixtures/bedrock/htdocs/app/themes/theme');

        $provider1 = Mockery::mock(RPTestServiceProvider1::class, [$app]);
        $provider1->expects('register')->once();
        $provider2 = Mockery::mock(RPTestServiceProvider2::class, [$app]);
        $provider2->expects('register')->once();

        $config = new Config();
        $config->set('app.providers', [$provider1, $provider2]);
        $app->bind('config', $config);

        $registerProvidersBootstrapper = new RegisterProviders();
        $registerProvidersBootstrapper->bootstrap($app);
    }

    public function testShouldNotFallOverOnEmptyConfigData()
    {
        $app = new Application(__DIR__ . '/fixtures/bedrock/htdocs/app/themes/theme');

        $config = new Config();
        $app->bind('config', $config);

        $registerProvidersBootstrapper = new RegisterProviders();
        $registerProvidersBootstrapper->bootstrap($app);

        $this->addToAssertionCount(1); // does not throw an exception
    }

    public function testRegistersAllProvidersFoundInPackages()
    {
        @unlink(__DIR__ . __DIR__ . '/fixtures/bedrock-autoload-packages-providers/htdocs/app/themes/theme/boostrap/cache/packages.php');
        $app = Mockery::mock(Application::class . '[register]', [__DIR__ . '/fixtures/bedrock-autoload-packages-providers/htdocs/app/themes/theme']);

        $app->expects('register')
            ->once()
            ->with('FooProvider');

        $app->expects('register')
            ->once()
            ->with('BarProvider');

        $app->expects('register')
            ->once()
            ->with(LogServiceProvider::class);

        $config = new Config();
        $app->bind('config', $config);

        $registerProvidersBootstrapper = new RegisterProviders();
        $registerProvidersBootstrapper->bootstrap($app);
    }
}

class RPTestServiceProvider1 extends ServiceProvider
{
    public function register()
    {
    }
}

class RPTestServiceProvider2 extends ServiceProvider
{
    public function register()
    {
    }
}
