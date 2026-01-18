<?php

namespace themes\Wordpress\Framework\Core\Test\Commands;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Artisan\Commands\SeedCommand;
use themes\Wordpress\Framework\Core\Artisan\OutputStyle;
use themes\Wordpress\Framework\Core\Database\Seeder;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SeedCommandTest extends CommandMakerTestCase
{
    public function testHandle()
    {
        $input = new ArrayInput(['--force' => true]);
        $output = new NullOutput();

        $seeder = Mockery::mock(Seeder::class);
        $seeder->shouldReceive('setContainer')->once()->andReturnSelf();
        $seeder->shouldReceive('setCommand')->once()->andReturnSelf();
        $seeder->shouldReceive('__invoke')->once();

        $container = Mockery::mock(Application::class);
        $container->shouldReceive('call');
        $container->shouldReceive('environment')->once()->andReturn('testing');
        $container->shouldReceive('make')->with('DatabaseSeeder')->andReturn($seeder);
        $container->shouldReceive('make')->with(OutputStyle::class, Mockery::any())->andReturn(
            new OutputStyle($input, $output)
        );

        $command = new SeedCommand($container);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
    }
}
