<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FactoryMakeCommand extends MakeFromStubCommand
{
    protected string $signature = '
    make:factory 
    {name : The class name of the Factory}
    {--acf} : Is the factory an ACF Partial Factory ?
    ';

    protected string $description = 'Create a Factory';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $acf = $this->option('acf') !== false;

        // If it have sub folder
        if (Str::contains($name, '/')) {
            $explodePath = explode('/', $name);
            $name = $explodePath[count($explodePath) - 1];
            unset($explodePath[count($explodePath) - 1]);
            $path = implode('/', $explodePath);
        }

        $filePath = isset($path) ? "database/factories/{$path}/{$name}.php" : "database/factories/{$name}.php";

        // If a Factory already exists
        if (file_exists("{$this->app->basePath()}/{$filePath}")) {
            $output->writeln("<error>Factory {$name} already exists.</error>");

            return Command::FAILURE;
        }

        $nameWithoutFactory = Str::replaceLast('Factory', '', $name);
        $humanizeName = Str::snake($nameWithoutFactory, ' ');
        $stub = $acf ? file_get_contents(__DIR__ . '/stubs/Factory.Acf.stub') : file_get_contents(__DIR__ . '/stubs/Factory.stub');
        $stub = str_replace('DummyClassNameWithSpaces', $humanizeName, $stub);
        $stub = str_replace('DummyClassNameWithoutFactory', $nameWithoutFactory, $stub);
        $stub = str_replace('DummyClassName', $name, $stub);

        if (isset($path)) {
            $path = str_replace('/', '\\', $path);

            // Replace the namespace
            $stub = str_replace('namespace Database\Factories;', "namespace Database\Factories\\{$path};", $stub);
        }

        $this->createFile($filePath, $stub);

        $output->writeln($this->summary($name, $filePath, 'Factory'));

        return Command::SUCCESS;
    }
}
