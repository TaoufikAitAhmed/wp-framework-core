<?php

namespace themes\Wordpress\Framework\Core\Artisan;

use themes\Wordpress\Framework\Core\Config;
use Rareloop\Lumberjack\Application;

class RegisterCommands
{
    public function bootstrap(Application $app)
    {
        $config = $app->get(Config::class);
        $artisan = $app->get(Artisan::class);

        $commands = $config->get('artisan.commands', []);

        foreach ($commands as $command) {
            $artisan->console()->add($app->make($command));
        }
    }
}
