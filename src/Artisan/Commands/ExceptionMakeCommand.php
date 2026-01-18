<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExceptionMakeCommand extends MakeFromStubCommand
{
    protected string $signature = 'make:exception {name : The class name of the Exception}';

    protected string $description = 'Create a Exception';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        // If it have sub folder
        if (Str::contains($name, '/')) {
            $explodePath = explode('/', $name);
            $name = $explodePath[count($explodePath) - 1];
            unset($explodePath[count($explodePath) - 1]);
            $path = implode('/', $explodePath);
        }

        $filePath = isset($path) ? "app/Exceptions/{$path}/{$name}.php" : "app/Exceptions/{$name}.php";

        // If a Exception already exists
        if (file_exists("{$this->app->basePath()}/{$filePath}")) {
            $output->writeln("<error>Exception {$name} already exists.</error>");

            return Command::FAILURE;
        }

        $stub = file_get_contents(__DIR__ . '/stubs/Exception.stub');
        $stub = str_replace('DummyException', $name, $stub);

        if (isset($path)) {
            $path = str_replace('/', '\\', $path);

            // Replace the namespace
            $stub = str_replace('namespace App\Exceptions;', "namespace App\Exceptions\\{$path};", $stub);
        }

        $this->createFile($filePath, $stub);

        $output->writeln($this->summary($name, $filePath, 'Exception'));

        return Command::SUCCESS;
    }
}
