<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use themes\Wordpress\Framework\Core\Artisan\Concerns\ConfirmableTrait;
use themes\Wordpress\Framework\Core\Database\Seeder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SeedCommand extends Command
{
    use ConfirmableTrait;

    protected string $signature = 'db:seed 
    {class? : The class name of the root seeder} 
    {--class=Database\\Seeders\\DatabaseSeeder : The class name of the root seeder} 
    {--force : Force the operation to run when in production}';

    protected string $description = 'Seed the database with records.';

    /**
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $this->getSeeder()->__invoke();

        $this->info('Database seeding completed successfully.');

        return Command::SUCCESS;
    }

    /**
     * Get a seeder instance from the container.
     *
     * @return Seeder
     */
    protected function getSeeder(): Seeder
    {
        $class = $this->input->getArgument('class') ?? $this->input->getOption('class');

        if (!str_contains($class, '\\')) {
            $class = sprintf('Database\\Seeders\\%s', $class);
        }

        if (
            $class === 'Database\\Seeders\\DatabaseSeeder' &&
            !class_exists($class)
        ) {
            $class = 'DatabaseSeeder';
        }

        return $this->app->make($class)
                         ->setContainer($this->app)
                         ->setCommand($this);
    }
}
