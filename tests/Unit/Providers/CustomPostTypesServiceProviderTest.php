<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Providers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Config;
use themes\Wordpress\Framework\Core\Providers\CustomPostTypesServiceProvider;
use themes\Wordpress\Framework\Core\Test\Unit\Providers\Fixtures\SubNameSpace1\CustomPostInSubNameSpace1;
use themes\Wordpress\Framework\Core\Test\Unit\Providers\Fixtures\SubNameSpace2\CustomPostInSubNameSpace2;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Functions;

/**
 * @preserveGlobalState disabled
 */
class CustomPostTypesServiceProviderTest extends WordpressTestCase
{
    public function testTheProviderRegistersThePostTypesThatAreInASubNamespace()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('posttypes.register', [CustomPostInSubNameSpace1::class, CustomPostInSubNameSpace2::class]);

        Functions\expect('register_post_type')->times(2);

        $provider = new CustomPostTypesServiceProvider($app);
        $provider->boot($config);
    }
}
