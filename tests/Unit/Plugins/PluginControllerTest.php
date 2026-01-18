<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Plugins;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Config;
use themes\Wordpress\Framework\Core\Controller;
use themes\Wordpress\Framework\Core\Plugins\Concerns\HasPlugins;
use themes\Wordpress\Framework\Core\Plugins\Plugin;
use themes\Wordpress\Framework\Core\Plugins\PluginCleaner;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @preserveGlobalState disabled
 */
class PluginControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Application $app;

    public function testItInstantiatesPackageCleanerWithPluginsSetInConfiguration()
    {
        $config = new Config();
        $this->app->bind('config', $config);

        $config->set('plugins', [PluginController1::class, PluginController2::class, PluginController3::class]);

        $controller = $this->getMockBuilder(CleanAllPluginsController::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $reflectedMethod = new \ReflectionMethod(CleanAllPluginsController::class, 'getPluginCleaner');

        $reflectedMethod->setAccessible(true);

        $this->assertSame([PluginController1::class, PluginController2::class, PluginController3::class], $reflectedMethod->invoke($controller)->all());
    }

    public function testItCleansPlugins()
    {
        $mock = Mockery::mock(CleanAllPluginsController::class)->shouldAllowMockingProtectedMethods();
        $pluginCleaner = Mockery::mock(PluginCleaner::class . '[clean]', [[PluginController1::class, PluginController2::class, PluginController3::class]]);

        $mock
            ->shouldReceive('getPluginCleaner')
            ->atleast()
            ->times(1)
            ->andReturns($pluginCleaner);

        $pluginCleaner
            ->expects('clean')
            ->once()
            ->with(PluginController1::class);
        $pluginCleaner
            ->expects('clean')
            ->once()
            ->with(PluginController2::class);
        $pluginCleaner
            ->expects('clean')
            ->once()
            ->with(PluginController3::class);

        $reflectedController = new ReflectionClass(CleanAllPluginsController::class);
        $constructor = $reflectedController->getConstructor();
        $constructor->invoke($mock);
    }

    public function testItCleansPluginsButNotTaoufikneControllerNeeds()
    {
        $mock = Mockery::mock(CleanAllPluginsExceptOneController::class)->shouldAllowMockingProtectedMethods();
        $pluginCleaner = Mockery::mock(PluginCleaner::class . '[clean]', [[PluginController1::class, PluginController2::class, PluginController3::class]]);

        $mock
            ->shouldReceive('plugins')
            ->once()
            ->andReturn([PluginController2::class]);

        $mock
            ->shouldReceive('getPluginCleaner')
            ->atLeast()
            ->times(1)
            ->andReturn($pluginCleaner);

        $pluginCleaner
            ->expects('clean')
            ->once()
            ->with(PluginController1::class);
        $pluginCleaner
            ->expects('clean')
            ->never()
            ->with(PluginController2::class);
        $pluginCleaner
            ->expects('clean')
            ->once()
            ->with(PluginController3::class);

        $reflectedController = new ReflectionClass(CleanAllPluginsExceptOneController::class);
        $constructor = $reflectedController->getConstructor();
        $constructor->invoke($mock);
    }

    public function testItDoNotCallPluginsMethodIfTheHasPluginsTraitIsNotThere()
    {
        $mock = \Mockery::mock(PluginsMethodWithoutHasPluginsTraitController::class)->shouldAllowMockingProtectedMethods();

        $mock
            ->shouldReceive('getPluginCleaner')
            ->atLeast()
            ->times(1)
            ->andReturn(new PluginCleaner());

        $mock->expects('plugins')->never();

        $reflectedController = new ReflectionClass(PluginsMethodWithoutHasPluginsTraitController::class);
        $constructor = $reflectedController->getConstructor();
        $constructor->invoke($mock);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Application();
    }
}

class PluginController1 extends Plugin
{
    public function clean(): void
    {
    }
}

class PluginController2 extends Plugin
{
    public function clean(): void
    {
    }
}

class PluginController3 extends Plugin
{
    public function clean(): void
    {
    }
}

class CleanAllPluginsController extends Controller
{
}

class CleanAllPluginsExceptOneController extends Controller
{
    use HasPlugins;

    protected function plugins(): array
    {
        return [];
    }
}

class PluginsMethodWithoutHasPluginsTraitController extends Controller
{
    public function plugins(): array
    {
        return [PluginController2::class];
    }
}
