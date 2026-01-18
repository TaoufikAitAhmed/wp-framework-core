<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands\Acf;

use themes\Wordpress\Framework\Core\Artisan\Commands\Command;
use themes\Wordpress\Framework\Core\Artisan\Commands\MakeFromStubCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FieldMakeCommand extends MakeFromStubCommand
{
    protected string $signature = 'acf:field {name : The class name of the Field}';

    protected string $description = 'Create a new ACF field group.';

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

        $filePath = isset($path) ? "app/Acf/Fields/{$path}/{$name}.php" : "app/Acf/Fields/{$name}.php";

        // If an Acf Field already exists
        if (file_exists("{$this->app->basePath()}/{$filePath}")) {
            $output->writeln("<error>ACF Field {$name} already exists.</error>");

            return Command::FAILURE;
        }

        $stub = file_get_contents(__DIR__ . '/stubs/Field.stub');
        $stub = str_replace('DummyClass', $name, $stub);
        $stub = str_replace('DummySnake', Str::snake($name), $stub);
        $stub = str_replace('dummyCamel', Str::camel($name), $stub);

        if (isset($path)) {
            $path = str_replace('/', '\\', $path);

            // Replace the namespace
            $stub = str_replace('namespace App\Acf\Fields;', "namespace App\Acf\Fields\\{$path};", $stub);
        }

        $this->createFile($filePath, $stub);

        $output->writeln($this->summary($name, $filePath, 'ACF Field'));

        return Command::SUCCESS;
    }
}
