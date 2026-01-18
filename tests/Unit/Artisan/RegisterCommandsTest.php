<?php

namespace themes\Wordpress\Framework\Core\Test;

use themes\Wordpress\Framework\Core\Artisan\Artisan;
use themes\Wordpress\Framework\Core\Artisan\Commands\Command;
use themes\Wordpress\Framework\Core\Artisan\RegisterCommands;
use themes\Wordpress\Framework\Core\Config;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterCommandsTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @test
     */
    public function additional_commands_are_registered_from_config()
    {
        $config = new Config();
        $config->set('artisan.commands', [AnotherTestCommand::class]);

        $app = new Application();
        $app->bind(Config::class, $config);

        $kernal = new Artisan($app);

        $bootstrapper = new RegisterCommands();
        $bootstrapper->bootstrap($app);

        $this->assertTrue($kernal->console()->has((new AnotherTestCommand($app))->getName()));
    }
}

class AnotherTestCommand extends Command
{
    protected string $signature = 'test:command';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
