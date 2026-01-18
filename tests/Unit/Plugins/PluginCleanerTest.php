<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Plugins;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Controller;
use themes\Wordpress\Framework\Core\Plugins\Plugin;
use themes\Wordpress\Framework\Core\Plugins\PluginCleaner;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @preserveGlobalState disabled
 */
class PluginCleanerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testItThrowAnExceptionIfAClassDoesNotExist()
    {
        $pluginCleaner = new PluginCleaner(['azerty']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The azerty class does not exists.');

        $pluginCleaner->cleanAll();
    }

    public function testItThrowAnExceptionIfAPluginSetInArrayIsNotExtendingPlugin()
    {
        $pluginCleaner = new PluginCleaner([Plugin1::class, TestController1::class]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The themes\Wordpress\Framework\Core\Test\Unit\Plugins\TestController1 class is not an instance of Plugin.');

        $pluginCleaner->cleanAll();
    }

    public function testItCleansAllActivePlugins()
    {
        $pluginCleaner = \Mockery::mock(PluginCleaner::class . '[clean]', [[Plugin1::class, Plugin2::class]]);

        $pluginCleaner
            ->expects('clean')
            ->once()
            ->with(Plugin1::class);

        $pluginCleaner
            ->expects('clean')
            ->once()
            ->with(Plugin2::class);

        $pluginCleaner->cleanAll();
    }

    public function testCleanerNotCleaningAllPlugins()
    {
        $pluginCleaner = \Mockery::mock(PluginCleaner::class . '[clean]', [[Plugin1::class, Plugin2::class, Plugin3::class, Plugin4::class]]);

        $pluginCleaner
            ->expects('clean')
            ->once()
            ->with(Plugin1::class);

        $pluginCleaner
            ->expects('clean')
            ->once()
            ->with(Plugin2::class);

        $pluginCleaner
            ->expects('clean')
            ->never()
            ->with(Plugin3::class);

        $pluginCleaner
            ->expects('clean')
            ->never()
            ->with(Plugin4::class);

        $pluginCleaner->cleanAll([Plugin3::class, Plugin4::class]);
    }

    public function testCleanerCanCleanAPlugin()
    {
        $plugin = \Mockery::mock(Plugin1::class . '[clean]');

        $plugin->expects('clean')->once();

        $pluginCleaner = new PluginCleaner();
        $pluginCleaner->clean($plugin);
    }

    protected function setUp(): void
    {
        parent::setUp();
        new Application();
    }
}

class TestController1 extends Controller
{
}

class Plugin1 extends Plugin
{
    public function clean(): void
    {
    }
}

class Plugin2 extends Plugin
{
    public function clean(): void
    {
    }
}

class Plugin3 extends Plugin
{
    public function clean(): void
    {
    }
}

class Plugin4 extends Plugin
{
    public function clean(): void
    {
    }
}
