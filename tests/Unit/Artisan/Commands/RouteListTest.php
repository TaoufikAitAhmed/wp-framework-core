<?php

namespace themes\Wordpress\Framework\Core\Test\Commands;

use themes\Wordpress\Framework\Core\Artisan\Artisan;
use themes\Wordpress\Framework\Core\Artisan\Commands\RouteListCommand;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Router\Router;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class RouteListTest extends CommandMakerTestCase
{
    /**
     * @test
     */
    public function can_list_routes_with_closure()
    {
        $app = new Application();
        $artisan = $app->make(Artisan::class);
        $artisan->console()->add($app->make(RouteListCommand::class));
        $router = new Router($app);
        $app->bind(Router::class, $router);
        $router->get('/test/123', function () {
        })->name('MyRouteName');

        $output = $this->callArtisanCommand($artisan, 'route:list');
        $output = $output->fetch();

        $this->assertStringContainsString('/test/123', $output);
        $this->assertStringContainsString('Closure', $output);
        $this->assertStringContainsString('GET', $output);
        $this->assertStringContainsString('MyRouteName', $output);
    }

    /**
     * @test
     */
    public function can_list_routes_with_callable()
    {
        $app = new Application();
        $artisan = $app->make(Artisan::class);
        $artisan->console()->add($app->make(RouteListCommand::class));
        $router = new Router($app);
        $app->bind(Router::class, $router);
        $router->get('/test/123', [RouteListTestController::class, 'testStatic']);

        $output = $this->callArtisanCommand($artisan, 'route:list');
        $output = $output->fetch();

        $this->assertStringContainsString('/test/123', $output);
        $this->assertStringContainsString(RouteListTestController::class, $output);
        $this->assertStringContainsString('GET', $output);
    }

    /**
     * @test
     */
    public function can_list_multiple_methods()
    {
        $app = new Application();
        $artisan = $app->make(Artisan::class);
        $artisan->console()->add($app->make(RouteListCommand::class));
        $router = new Router($app);
        $app->bind(Router::class, $router);
        $router->map(['get', 'post'], '/test/123', function () {
        });

        $output = $this->callArtisanCommand($artisan, 'route:list');
        $output = $output->fetch();

        $this->assertStringContainsString('GET|POST', $output);
    }
}

class RouteListTestController
{
    public function test()
    {
    }

    public static function testStatic()
    {
    }
}
