<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Providers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Config;
use themes\Wordpress\Framework\Core\Providers\CustomTaxonomyServiceProvider;
use themes\Wordpress\Framework\Core\Term;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Functions;

/**
 * @preserveGlobalState disabled
 */
class CustomTaxonomySeviceProviderTest extends WordpressTestCase
{
    public function testShouldCallRegisterTaxonomyForEachConfiguredTaxonomy()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('taxonomies.register', [CustomTaxonomy1::class, CustomTaxonomy2::class]);

        Functions\expect('register_taxonomy')->times(2);

        $provider = new CustomTaxonomyServiceProvider($app);
        $provider->boot($config);
    }
}

class CustomTaxonomy1 extends Term
{
    public static function getTaxonomyType(): string
    {
        return 'custom_taxonomy_1';
    }

    public static function getTaxonomyObjectTypes(): array
    {
        return ['post'];
    }

    protected static function getTaxonomyConfig(): array
    {
        return [
            'not' => 'empty',
        ];
    }
}

class CustomTaxonomy2 extends Term
{
    public static function getTaxonomyType(): string
    {
        return 'custom_taxonomy_1';
    }

    public static function getTaxonomyObjectTypes(): array
    {
        return ['post'];
    }

    protected static function getTaxonomyConfig(): array
    {
        return [
            'not' => 'empty',
        ];
    }
}
