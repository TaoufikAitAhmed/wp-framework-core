<?php

namespace themes\Wordpress\Framework\Core\Http;

use themes\Wordpress\Framework\Core\Bootstrappers\LoadConfiguration;
use themes\Wordpress\Framework\Core\Bootstrappers\RegisterAliases;
use themes\Wordpress\Framework\Core\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\RegisterExceptionHandler;
use Rareloop\Lumberjack\Bootstrappers\RegisterFacades;
use Rareloop\Lumberjack\Bootstrappers\RegisterRequestHandler;

class Lumberjack
{
    protected array $bootstrappers = [
        LoadConfiguration::class,
        RegisterExceptionHandler::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
        RegisterAliases::class,
        RegisterRequestHandler::class,
    ];

    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function bootstrap()
    {
        $this->app->bootstrapWith($this->bootstrappers());
    }

    protected function bootstrappers(): array
    {
        return $this->bootstrappers;
    }
}
