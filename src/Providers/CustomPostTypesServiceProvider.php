<?php

namespace themes\Wordpress\Framework\Core\Providers;

use themes\Wordpress\Framework\Core\Config;

class CustomPostTypesServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        $postTypesToRegister = $config->get('posttypes.register');

        foreach ($postTypesToRegister as $postType) {
            $postType::register();
        }
    }
}
