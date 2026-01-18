<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Database;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Artisan\Commands\Command;
use themes\Wordpress\Framework\Core\Database\Seeder;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Mockery;
use Mockery\Mock;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @preserveGlobalState disabled
 */
class SeederTest extends WordpressTestCase
{
    public function testCallResolveTheClassAndCallsRun()
    {
        $seeder = new TestSeeder();
        $seeder->setContainer($container = Mockery::mock(Application::class));
        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('writeln')->once();
        $command = Mockery::mock(Command::class);
        $command->shouldReceive('getOutput')->once()->andReturn($output);
        $seeder->setCommand($command);
        $container->shouldReceive('make')->once()->with('ClassName')->andReturn($child = Mockery::mock(Seeder::class));
        $child->shouldReceive('setContainer')->once()->with($container)->andReturn($child);
        $child->shouldReceive('setCommand')->once()->with($command)->andReturn($child);
        $child->shouldReceive('__invoke')->once();
        $command->shouldReceive('getOutput')->once()->andReturn($output);
        $output->shouldReceive('writeln')->once();

        $seeder->call('ClassName');
    }

    public function testSetContainer()
    {
        $seeder = new TestSeeder();
        $container = Mockery::mock(Application::class);
        $this->assertEquals($seeder->setContainer($container), $seeder);
    }

    public function testSetCommand()
    {
        $seeder = new TestSeeder();
        $command = Mockery::mock(Command::class);
        $this->assertEquals($seeder->setCommand($command), $seeder);
    }

    public function testInjectDependenciesOnRunMethod()
    {
        $container = Mockery::mock(Application::class);

        $container->shouldReceive('call');

        $seeder = new TestDepsSeeder();
        $seeder->setContainer($container);

        $seeder->__invoke();

        $container->shouldHaveReceived('call')->once()->with([$seeder, 'run'], []);
    }

    public function testSendParamsOnCallMethodWithDeps()
    {
        $container = Mockery::mock(Application::class);
        $container->shouldReceive('call');

        $seeder = new TestDepsSeeder();
        $seeder->setContainer($container);

        $seeder->__invoke(['test1', 'test2']);

        $container->shouldHaveReceived('call')->once()->with([$seeder, 'run'], ['test1', 'test2']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

class TestSeeder extends Seeder
{
    public function run()
    {
        //
    }
}

class TestDepsSeeder extends Seeder
{
    public function run(Mock $someDependency, $someParam = '')
    {
        //
    }
}
