<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Providers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Assets\Contracts\Manager as ManagerContract;
use themes\Wordpress\Framework\Core\Assets\Contracts\Manifest as ManifestContract;
use themes\Wordpress\Framework\Core\Assets\Manager;
use themes\Wordpress\Framework\Core\Assets\Manifest;
use themes\Wordpress\Framework\Core\Config;
use themes\Wordpress\Framework\Core\Providers\AssetsServiceProvider;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Functions;

/**
 * @preserveGlobalState disabled
 */
class AssetsServiceProviderTest extends WordpressTestCase
{
    /**
     * Array of needed configuration in the `config/theme.php` file.
     *
     * @var array|string[][]
     */
    protected array $mandatoryThemeConfig = [
        'directory' => '/dist',
    ];

    public function testManagerIsRegistered()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();
        $app->bind('config', $config);

        $config->set('assets', array_merge_recursive($this->mandatoryThemeConfig));

        $provider = new AssetsServiceProvider($app);
        $provider->register();

        $this->assertTrue($app->has('assets'));
        $this->assertInstanceOf(Manager::class, $app->get('assets'));
        $this->assertSame($app->get('assets'), $app->get(Manager::class));
        $this->assertSame($app->get('assets'), $app->get(ManagerContract::class));
        $this->assertSame('/dist', $app->get('assets')->path());
        $this->assertSame(null, $app->get('assets')->version());
    }

    public function testManagerIsRegisteredWithManifest()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();
        $app->bind('config', $config);

        $config->set(
            'assets',
            array_merge_recursive($this->mandatoryThemeConfig, [
                'manifest' => [
                    'enable' => true,
                    'path'   => 'manifest.json',
                ],
            ]),
        );

        $provider = new AssetsServiceProvider($app);
        $provider->register();

        $this->assertTrue($app->has('assets'));
        $this->assertTrue($app->has('assets.manifest'));
        $this->assertInstanceOf(Manager::class, $app->get('assets'));
        $this->assertInstanceOf(Manifest::class, $app->get('assets')->getManifest());
        $this->assertInstanceOf(Manifest::class, $app->get('assets.manifest'));
        $this->assertSame($app->get('assets.manifest'), $app->get(Manifest::class));
        $this->assertSame($app->get('assets.manifest'), $app->get(ManifestContract::class));

        $this->assertSame('/dist', $app->get('assets')->path());
        $this->assertSame(null, $app->get('assets')->version());
    }

    public function testAssetsAreEnqueuedFromConfiguration()
    {
        Functions\when('get_stylesheet_directory_uri')->justReturn('https://example.com/app/themes/theme');

        $app = new Application(__DIR__ . '/..');

        $config = new Config();
        $app->bind('config', $config);

        $config->set(
            'assets',
            array_merge_recursive($this->mandatoryThemeConfig, [
                'styles'  => [
                    'main' => 'css/app.css',
                ],
                'scripts' => [
                    'main' => 'js/app.js',
                ],
            ]),
        );

        $mock = $this->getMockBuilder(AssetsServiceProvider::class)
                     ->disableOriginalConstructor()
                     ->setMethods(['enqueue'])
                     ->getMock();

        $mock->expects($this->once())->method('enqueue');

        $mock->boot($config);
    }

    public function testStylesFromConfigAreUsedInEnqueuedAssets()
    {
        Functions\when('get_stylesheet_directory_uri')->justReturn('https://example.com/app/themes/theme');

        $app = new Application(__DIR__ . '/..');

        $config = new Config();
        $app->bind('config', $config);

        $config->set(
            'assets',
            array_merge_recursive($this->mandatoryThemeConfig, [
                'styles'  => [
                    'main' => 'css/app.css',
                ],
                'scripts' => [
                    'main' => 'js/app.js',
                ],
            ]),
        );

        $assetsServiceProvider = new AssetsServiceProvider($app);
        $assetsServiceProvider->boot($config);
        $this->assertSame(['main' => 'css/app.css'], $assetsServiceProvider->css());
    }

    public function testScriptsFromConfigAreUsedInEnqueuedAssets()
    {
        Functions\when('get_stylesheet_directory_uri')->justReturn('https://example.com/app/themes/theme');

        $app = new Application(__DIR__ . '/..');

        $config = new Config();
        $app->bind('config', $config);

        $config->set(
            'assets',
            array_merge_recursive($this->mandatoryThemeConfig, [
                'styles'  => [
                    'main' => 'css/app.css',
                ],
                'scripts' => [
                    'main' => 'js/app.js',
                ],
            ]),
        );

        $assetsServiceProvider = new AssetsServiceProvider($app);
        $assetsServiceProvider->boot($config);
        $this->assertSame(['main' => 'js/app.js'], $assetsServiceProvider->js());
    }

    public function testStylesAreAnEmptyArray()
    {
        Functions\when('get_stylesheet_directory_uri')->justReturn('https://example.com/app/themes/theme');

        $app = new Application(__DIR__ . '/..');

        $config = new Config();
        $app->bind('config', $config);

        $config->set(
            'assets',
            array_merge_recursive($this->mandatoryThemeConfig, [
                'scripts' => [
                    'main' => 'js/app.js',
                ],
            ]),
        );

        $assetsServiceProvider = new AssetsServiceProvider($app);
        $assetsServiceProvider->boot($config);
        $this->assertSame([], $assetsServiceProvider->css());
    }

    public function testScriptsAreAnEmptyArray()
    {
        Functions\when('get_stylesheet_directory_uri')->justReturn('https://example.com/app/themes/theme');

        $app = new Application(__DIR__ . '/..');

        $config = new Config();
        $app->bind('config', $config);

        $config->set(
            'assets',
            array_merge_recursive($this->mandatoryThemeConfig, [
                'styles' => [
                    'main' => 'css/app.css',
                ],
            ]),
        );

        $assetsServiceProvider = new AssetsServiceProvider($app);
        $assetsServiceProvider->boot($config);
        $this->assertSame([], $assetsServiceProvider->js());
    }
}
