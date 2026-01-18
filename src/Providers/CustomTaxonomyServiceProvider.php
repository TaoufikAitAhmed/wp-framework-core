<?php

namespace themes\Wordpress\Framework\Core\Providers;

use themes\Wordpress\Framework\Core\Config;

class CustomTaxonomyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @param Config $config
     *
     * @return void
     */
    public function boot(Config $config)
    {
        $taxonomiesToRegister = $config->get('taxonomies.register');

        foreach ($taxonomiesToRegister as $taxonomy) {
            $taxonomy::register();
        }
    }
}
