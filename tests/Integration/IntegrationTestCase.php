<?php

namespace themes\Wordpress\Framework\Core\Test\Integration;

use Rareloop\Lumberjack\Facades\Config;
use WP_Rewrite;
use WP_UnitTestCase;

class IntegrationTestCase extends WP_UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        reset_theme();
        Config::set('app.environment', 'testing');

        /** @var WP_Rewrite $wp_rewrite */
        global $wp_rewrite;

        /**
         * Change the permalink structure
         */
        $wp_rewrite->init();
        $wp_rewrite->set_permalink_structure('/%category%/%postname%/');
    }
}
