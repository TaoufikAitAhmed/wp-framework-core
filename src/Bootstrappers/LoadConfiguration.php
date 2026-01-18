<?php

namespace themes\Wordpress\Framework\Core\Bootstrappers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Config;
use Rareloop\Lumberjack\Config as LumberjackConfig;

class LoadConfiguration
{
    public function bootstrap(Application $app)
    {
        $config = new Config($app->configPath());

        $app->bind('config', $config);
        $app->bind(LumberjackConfig::class, $config);
        $app->bind(Config::class, $config);
    }
}
