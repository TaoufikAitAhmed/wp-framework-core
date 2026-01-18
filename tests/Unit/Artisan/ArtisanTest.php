<?php

namespace themes\Wordpress\Framework\Core\Test;

use themes\Wordpress\Framework\Core\Artisan\Artisan;
use themes\Wordpress\Framework\Core\Artisan\Commands\Command;
use themes\Wordpress\Framework\Core\Artisan\RegisterCommands;
use themes\Wordpress\Framework\Core\Bootstrappers\LoadConfiguration;
use themes\Wordpress\Framework\Core\Bootstrappers\RegisterProviders;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\RegisterExceptionHandler;
use Rareloop\Lumberjack\Bootstrappers\RegisterFacades;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ArtisanTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @test
     */
    public function bootstrap_should_pass_bootstrappers_to_app()
    {
        $app = Mockery::mock(Application::class . '[bootstrapWith]');
        $app->shouldReceive('bootstrapWith')
            ->with([
                RegisterExceptionHandler::class,
                LoadConfiguration::class,
                RegisterFacades::class,
                RegisterProviders::class,
                BootProviders::class,
                RegisterCommands::class,
            ])
            ->once();

        $kernal = new Artisan($app);
        $kernal->bootstrap();
    }

    /**
     * @test
     */
    public function artisan_is_registered_in_the_container_when_created()
    {
        $app = new Application();
        $kernal = new Artisan($app);

        $this->assertInstanceOf(Artisan::class, $app->get(Artisan::class));
        $this->assertSame($kernal, $app->get(Artisan::class));
    }

    /**
     * @test
     */
    public function can_access_console()
    {
        $app = new Application();
        $kernal = new Artisan($app);

        $console = $kernal->console();

        $this->assertInstanceOf(ConsoleApplication::class, $console);
    }

    /**
     * @test
     */
    public function default_commands_are_registered_on_the_console()
    {
        $app = Mockery::mock(Application::class . '[bootstrapWith]');
        $app->shouldReceive('bootstrapWith');
        $kernal = Mockery::mock(Artisan::class . '[defaultCommands]', [$app]);
        $kernal
            ->shouldReceive('defaultCommands')
            ->once()
            ->andReturn([TestCommand::class]);

        $kernal->bootstrap();

        $this->assertTrue($kernal->console()->has((new TestCommand($app))->getName()));
    }
}

class TestCommand extends Command
{
    protected string $signature = 'test:command';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
