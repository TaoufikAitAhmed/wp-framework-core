<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets;

use themes\Wordpress\Framework\Core\Assets\Manager;
use themes\Wordpress\Framework\Core\Assets\Manifest;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Functions;
use Rareloop\Lumberjack\Application;

/**
 * @preserveGlobalState disabled
 */
class AssetFunctionTest extends WordpressTestCase
{
    public function testAssetFunctionIsRegistered()
    {
        $this->assertTrue(function_exists('asset'));
    }

    public function testItRetrieveAsset()
    {
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        $this->assertEquals('https://example.com/app/themes/theme/dist/css/app.css', asset('css/app.css'));
    }

    public function testItRetrieveAssetWithManifest()
    {
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist'], new Manifest(sprintf('%s/Fixtures/manifest.json', __DIR__))));

        $this->assertEquals('https://example.com/app/themes/theme/dist/css/app-7253142354564564654.css', asset('css/app.css'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->handleGetStylesheetDirectoryUri();
        include_once sprintf('%s/../../../src/functions.php', __DIR__);
    }

    /**
     * Handle `get_stylesheet_directory_uri` function to return
     * https://example.com
     */
    protected function handleGetStylesheetDirectoryUri()
    {
        Functions\when('get_stylesheet_directory_uri')->justReturn('https://example.com/app/themes/theme');
    }
}
