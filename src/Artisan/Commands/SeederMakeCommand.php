<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SeederMakeCommand extends MakeFromStubCommand
{
    protected string $signature = 'make:seeder {name : The class name of the Seeder}';

    protected string $description = 'Create a Seeder';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        // If it have sub folder
        if (Str::contains($name, '/')) {
            $explodePath = explode('/', $name);
            $name = $explodePath[count($explodePath) - 1];
            unset($explodePath[count($explodePath) - 1]);
            $path = implode('/', $explodePath);
        }

        $filePath = isset($path) ? "database/seeders/{$path}/{$name}.php" : "database/seeders/{$name}.php";

        // If a Seeder already exists
        if (file_exists("{$this->app->basePath()}/{$filePath}")) {
            $output->writeln("<error>Seeder {$name} already exists.</error>");

            return Command::FAILURE;
        }

        $stub = file_get_contents(__DIR__ . '/stubs/Seeder.stub');
        $stub = str_replace('DummyClassName', $name, $stub);

        if (isset($path)) {
            $path = str_replace('/', '\\', $path);

            // Replace the namespace
            $stub = str_replace('namespace Database\Seeders;', "namespace Database\Seeders\\{$path};", $stub);
        }

        $this->createFile($filePath, $stub);

        $output->writeln($this->summary($name, $filePath, 'Seeder'));

        return Command::SUCCESS;
    }
}
