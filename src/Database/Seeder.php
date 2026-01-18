<?php

namespace themes\Wordpress\Framework\Core\Database;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Artisan\Commands\Command;
use Illuminate\Support\Arr;
use InvalidArgumentException;

abstract class Seeder
{
    /**
     * Seeders that have been called at least one time.
     *
     * @var array
     */
    protected static array $called = [];

    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $container;

    /**
     * The console command instance.
     *
     * @var Command
     */
    protected Command $command;

    /**
     * Run the given seeder class.
     *
     * @param array|string $class
     * @param bool         $silent
     * @param array        $parameters
     *
     * @return $this
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    public function call($class, bool $silent = false, array $parameters = []): self
    {
        $classes = Arr::wrap($class);

        foreach ($classes as $class) {
            $seeder = $this->resolve($class);

            $name = get_class($seeder);

            if ($silent === false && isset($this->command)) {
                $this->command->getOutput()->writeln("<comment>Seeding:</comment> {$name}");
            }

            $startTime = microtime(true);

            $seeder->__invoke($parameters);

            $runTime = number_format((microtime(true) - $startTime) * 1000, 2);

            if ($silent === false && isset($this->command)) {
                $this->command->getOutput()->writeln("<info>Seeded:</info>  {$name} ({$runTime}ms)");
            }

            static::$called[] = $class;
        }

        return $this;
    }

    /**
     * Run the given seeder class.
     *
     * @param array|string $class
     * @param array        $parameters
     *
     * @return void
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    public function callWith($class, array $parameters = [])
    {
        $this->call($class, false, $parameters);
    }

    /**
     * Silently run the given seeder class.
     *
     * @param array|string $class
     * @param array        $parameters
     *
     * @return void
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    public function callSilent($class, array $parameters = [])
    {
        $this->call($class, true, $parameters);
    }

    /**
     * Run the given seeder class once.
     *
     * @param array|string $class
     * @param bool         $silent
     * @param array        $parameters
     *
     * @return void
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    public function callOnce($class, bool $silent, array $parameters = [])
    {
        if (in_array($class, static::$called)) {
            return;
        }

        $this->call($class, $silent, $parameters);
    }

    /**
     * Set the IoC container instance.
     *
     * @param Application $container
     *
     * @return $this
     */
    public function setContainer(Application $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set the console command instance.
     *
     * @param Command $command
     *
     * @return $this
     */
    public function setCommand(Command $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Run the database seeds.
     *
     * @param array $parameters
     *
     * @return mixed
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    public function __invoke(array $parameters = [])
    {
        if (!method_exists($this, 'run')) {
            throw new InvalidArgumentException(sprintf('Method [run] missing from %s', get_class($this)));
        }

        $callback = fn () => isset($this->container)
            ? $this->container->call([$this, 'run'], $parameters)
            : $this->run(...$parameters);

        return $callback();
    }

    /**
     * Resolve an instance of the given seeder class.
     *
     * @param string $class
     *
     * @return Seeder
     */
    protected function resolve(string $class): self
    {
        if (isset($this->container)) {
            $instance = $this->container->make($class);

            $instance->setContainer($this->container);
        } else {
            $instance = new $class();
        }

        if (isset($this->command)) {
            $instance->setCommand($this->command);
        }

        return $instance;
    }
}
