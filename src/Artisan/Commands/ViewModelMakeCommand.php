<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ViewModelMakeCommand extends MakeFromStubCommand
{
    protected string $signature = 'make:viewmodel {name : The class name of the View Model}';

    protected string $description = 'Create a ViewModel';

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

        $filePath = isset($path) ? "app/ViewModels/{$path}/{$name}.php" : "app/ViewModels/{$name}.php";

        // If a View Model already exists
        if (file_exists("{$this->app->basePath()}/{$filePath}")) {
            $output->writeln("<error>View Model {$name} already exists.</error>");

            return Command::FAILURE;
        }

        $stub = file_get_contents(__DIR__ . '/stubs/ViewModel.stub');
        $stub = str_replace('DummyViewModel', $name, $stub);

        if (isset($path)) {
            $path = str_replace('/', '\\', $path);

            // Replace the namespace
            $stub = str_replace('namespace App\ViewModels;', "namespace App\ViewModels\\{$path};", $stub);
        }

        $this->createFile($filePath, $stub);

        $output->writeln($this->summary($name, $filePath, 'View Model'));

        return Command::SUCCESS;
    }
}
