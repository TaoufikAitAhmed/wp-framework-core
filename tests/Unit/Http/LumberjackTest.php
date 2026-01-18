<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Http;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Bootstrappers\LoadConfiguration;
use themes\Wordpress\Framework\Core\Bootstrappers\RegisterAliases;
use themes\Wordpress\Framework\Core\Bootstrappers\RegisterProviders;
use themes\Wordpress\Framework\Core\Http\Lumberjack;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\RegisterExceptionHandler;
use Rareloop\Lumberjack\Bootstrappers\RegisterFacades;
use Rareloop\Lumberjack\Bootstrappers\RegisterRequestHandler;

class LumberjackTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testBootstrapShouldPassBootstrappersToApp()
    {
        $app = Mockery::mock(Application::class . '[bootstrapWith]');

        $app->expects('bootstrapWith')
            ->with([
                LoadConfiguration::class,
                RegisterExceptionHandler::class,
                RegisterFacades::class,
                RegisterProviders::class,
                BootProviders::class,
                RegisterAliases::class,
                RegisterRequestHandler::class,
            ])
            ->once();

        $kernal = new Lumberjack($app);
        $kernal->bootstrap();
    }
}
