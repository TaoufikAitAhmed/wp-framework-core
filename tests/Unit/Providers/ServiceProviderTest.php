<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Providers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Artisan\Artisan;
use themes\Wordpress\Framework\Core\Artisan\Commands\Command;
use themes\Wordpress\Framework\Core\Config;
use themes\Wordpress\Framework\Core\Providers\ServiceProvider;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Filters;
use Mockery;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Loader\FilesystemLoader;

/**
 * @preserveGlobalState disabled
 */
class ServiceProviderTest extends WordpressTestCase
{
    protected string $vfsStreamDirectoryName = 'exampleDir';

    public function testPublishableServiceProviders()
    {
        $toPublish = ServiceProvider::publishableProviders();
        $expected = [ServiceProviderForTestingOne::class, ServiceProviderForTestingTwo::class];
        $this->assertEquals($expected, $toPublish, 'Publishable service providers do not return expected set of providers.');
    }

    public function testPublishableGroups()
    {
        $toPublish = ServiceProvider::publishableGroups();
        $this->assertEquals(['some_tag', 'tag_one', 'tag_two'], $toPublish, 'Publishable groups do not return expected set of groups.');
    }

    public function testSimpleAssetsArePublishedCorrectly()
    {
        $toPublish = ServiceProvider::pathsToPublish(ServiceProviderForTestingOne::class);
        $this->assertArrayHasKey('source/unmarked/one', $toPublish, 'Service provider does not return expected published path key.');
        $this->assertArrayHasKey('source/tagged/one', $toPublish, 'Service provider does not return expected published path key.');
        $this->assertEquals(
            [
                'source/unmarked/one'    => 'destination/unmarked/one',
                'source/tagged/one'      => 'destination/tagged/one',
                'source/tagged/multiple' => 'destination/tagged/multiple',
            ],
            $toPublish,
            'Service provider does not return expected set of published paths.',
        );
    }

    public function testMultipleAssetsArePublishedCorrectly()
    {
        $toPublish = ServiceProvider::pathsToPublish(ServiceProviderForTestingTwo::class);
        $this->assertArrayHasKey('source/unmarked/two/a', $toPublish, 'Service provider does not return expected published path key.');
        $this->assertArrayHasKey('source/unmarked/two/b', $toPublish, 'Service provider does not return expected published path key.');
        $this->assertArrayHasKey('source/unmarked/two/c', $toPublish, 'Service provider does not return expected published path key.');
        $this->assertArrayHasKey('source/tagged/two/a', $toPublish, 'Service provider does not return expected published path key.');
        $this->assertArrayHasKey('source/tagged/two/b', $toPublish, 'Service provider does not return expected published path key.');
        $expected = [
            'source/unmarked/two/a' => 'destination/unmarked/two/a',
            'source/unmarked/two/b' => 'destination/unmarked/two/b',
            'source/unmarked/two/c' => 'destination/tagged/two/a',
            'source/tagged/two/a'   => 'destination/tagged/two/a',
            'source/tagged/two/b'   => 'destination/tagged/two/b',
        ];
        $this->assertEquals($expected, $toPublish, 'Service provider does not return expected set of published paths.');
    }

    public function testSimpleTaggedAssetsArePublishedCorrectly()
    {
        $toPublish = ServiceProvider::pathsToPublish(ServiceProviderForTestingOne::class, 'some_tag');
        $this->assertArrayNotHasKey('source/tagged/two/a', $toPublish, 'Service provider does return unexpected tagged path key.');
        $this->assertArrayNotHasKey('source/tagged/two/b', $toPublish, 'Service provider does return unexpected tagged path key.');
        $this->assertArrayHasKey('source/tagged/one', $toPublish, 'Service provider does not return expected tagged path key.');
        $this->assertEquals(
            ['source/tagged/one' => 'destination/tagged/one'],
            $toPublish,
            'Service provider does not return expected set of published tagged paths.',
        );
    }

    public function testMultipleTaggedAssetsArePublishedCorrectly()
    {
        $toPublish = ServiceProvider::pathsToPublish(ServiceProviderForTestingTwo::class, 'some_tag');
        $this->assertArrayHasKey('source/tagged/two/a', $toPublish, 'Service provider does not return expected tagged path key.');
        $this->assertArrayHasKey('source/tagged/two/b', $toPublish, 'Service provider does not return expected tagged path key.');
        $this->assertArrayNotHasKey('source/tagged/one', $toPublish, 'Service provider does return unexpected tagged path key.');
        $this->assertArrayNotHasKey('source/unmarked/two/c', $toPublish, 'Service provider does return unexpected tagged path key.');
        $expected = [
            'source/tagged/two/a' => 'destination/tagged/two/a',
            'source/tagged/two/b' => 'destination/tagged/two/b',
        ];
        $this->assertEquals($expected, $toPublish, 'Service provider does not return expected set of published tagged paths.');
    }

    public function testMultipleTaggedAssetsAreMergedCorrectly()
    {
        $toPublish = ServiceProvider::pathsToPublish(null, 'some_tag');
        $this->assertArrayHasKey('source/tagged/two/a', $toPublish, 'Service provider does not return expected tagged path key.');
        $this->assertArrayHasKey('source/tagged/two/b', $toPublish, 'Service provider does not return expected tagged path key.');
        $this->assertArrayHasKey('source/tagged/one', $toPublish, 'Service provider does not return expected tagged path key.');
        $this->assertArrayNotHasKey('source/unmarked/two/c', $toPublish, 'Service provider does return unexpected tagged path key.');
        $expected = [
            'source/tagged/one'   => 'destination/tagged/one',
            'source/tagged/two/a' => 'destination/tagged/two/a',
            'source/tagged/two/b' => 'destination/tagged/two/b',
        ];
        $this->assertEquals($expected, $toPublish, 'Service provider does not return expected set of published tagged paths.');
    }

    public function testArtisanCommandsAreRegistered()
    {
        $app = new Application();
        $serviceProvider = new ServiceProviderForTestingArtisanCommand($app);

        $kernal = new Artisan($app);

        $serviceProvider->boot();

        $this->assertTrue($kernal->console()->has((new ArtisanTestCommand($app))->getName()));
    }

    public function testLoadViewsFromCreateTimberNamespaceLinkingToTheVendorPackageView()
    {
        $app = new Application(__DIR__ . '/__fixtures__/load-views-from/package');
        $serviceProvider = new ServiceProviderForTestingWithLoadViewsFromPackage($app);

        $loader = Mockery::mock(FilesystemLoader::class . '[addPath]');

        $loader
            ->expects('addPath')
            ->once()
            ->with(__DIR__ . '/__fixtures__/load-views-from/package/vendor/resources/views', 'package');

        Filters\expectAdded('timber/loader/loader')
            ->once()
            ->whenHappen(fn ($callback) => $callback($loader));

        $serviceProvider->boot();
    }

    public function testLoadViewsFromCreateTimberNamespaceLinkingToTheUserView()
    {
        $app = new Application(__DIR__ . '/__fixtures__/load-views-from/user');
        $serviceProvider = new ServiceProviderForTestingWithLoadViewsFromUser($app);

        $loader = Mockery::mock(FilesystemLoader::class . '[addPath]');

        $loader
            ->expects('addPath')
            ->once()
            ->with(__DIR__ . '/__fixtures__/load-views-from/user/resources/views/vendor/package', 'package');

        Filters\expectAdded('timber/loader/loader')
            ->once()
            ->whenHappen(fn ($callback) => $callback($loader));

        $serviceProvider->boot();
    }

    public function testCanMergeConfigFromAFile()
    {
        $config = new Config();
        $app = new Application();
        $app->bind(Config::class, $config);
        $provider = new TestServiceProvider($app);

        $provider->mergeConfigFrom(__DIR__ . '/__fixtures__/config/another.php', 'another');

        $this->assertSame(123, $config->get('another.test'));
    }

    public function testExistingConfigTakesPriorityOverMergedValues()
    {
        $config = new Config();
        $app = new Application();
        $app->bind(Config::class, $config);
        $provider = new TestServiceProvider($app);

        $config->set('another.test', 456);
        $provider->mergeConfigFrom(__DIR__ . '/__fixtures__/config/another.php', 'another');

        $this->assertSame(456, $config->get('another.test'));
    }

    protected function setUp(): void
    {
        ServiceProvider::$publishes = [];
        ServiceProvider::$publishGroups = [];

        $app = Mockery::mock(Application::class)->makePartial();
        $one = new ServiceProviderForTestingOne($app);
        $one->boot();
        $two = new ServiceProviderForTestingTwo($app);
        $two->boot();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

class TestServiceProvider extends ServiceProvider
{
}

class ServiceProviderForTestingOne extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->publishes(['source/unmarked/one' => 'destination/unmarked/one']);
        $this->publishes(['source/tagged/one' => 'destination/tagged/one'], 'some_tag');
        $this->publishes(['source/tagged/multiple' => 'destination/tagged/multiple'], ['tag_one', 'tag_two']);
    }
}

class ServiceProviderForTestingTwo extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->publishes(['source/unmarked/two/a' => 'destination/unmarked/two/a']);
        $this->publishes(['source/unmarked/two/b' => 'destination/unmarked/two/b']);
        $this->publishes(['source/unmarked/two/c' => 'destination/tagged/two/a']);
        $this->publishes(['source/tagged/two/a' => 'destination/tagged/two/a'], 'some_tag');
        $this->publishes(['source/tagged/two/b' => 'destination/tagged/two/b'], 'some_tag');
    }
}

class ServiceProviderForTestingWithLoadViewsFromPackage extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/__fixtures__/load-views-from/package/vendor/resources/views', 'package');
    }
}

class ServiceProviderForTestingWithLoadViewsFromUser extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/__fixtures__/load-views-from/user/vendor/package/resources/views', 'package');
    }
}

class ArtisanTestCommand extends Command
{
    protected string $signature = 'test:command';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}

class ServiceProviderForTestingArtisanCommand extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->commands([ArtisanTestCommand::class]);
    }
}
