<?php

namespace themes\Wordpress\Framework\Core\Test;

use themes\Wordpress\Framework\Core\Artisan\Commands\Command;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;

class CommandTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @test
     */
    public function can_create_a_command_with_a_name()
    {
        $app = new Application();
        $command = new CommandWithName($app);

        $this->assertSame('test:command', $command->getName());
    }

    /**
     * @test
     */
    public function can_create_a_command_from_signature_variable()
    {
        $app = new Application();
        $command = new CommandWithSignature($app);

        $definition = $command->getDefinition();

        $this->assertSame('test:command', $command->getName());
        $this->assertTrue($definition->hasOption('option'));
    }

    /**
     * @test
     */
    public function can_add_description_from_class_variable()
    {
        $app = new Application();
        $command = new CommandWithDescription($app);

        $this->assertSame('testing123', $command->getDescription());
    }
}

class CommandWithName extends Command
{
    protected string $signature = 'test:command';
}

class CommandWithSignature extends Command
{
    protected string $signature = 'test:command {name} {--option}';
}

class CommandWithDescription extends Command
{
    protected string $signature = 'test:command';

    protected string $description = 'testing123';
}
