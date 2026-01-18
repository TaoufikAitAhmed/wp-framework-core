<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerMakeCommand extends MakeFromStubCommand
{
    protected string $signature = 'make:controller {name : The class name of the Controller} {--template=false : Is a Wordpress template, or the name of the Wordpress template}';

    protected string $description = 'Create a Controller';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $template = $input->getOption('template') === 'false' ? false : $input->getOption('template');
        if (is_null($template)) {
            $template = true;
        }

        // If it have sub folder
        if (Str::contains($name, '/')) {
            $explodePath = explode('/', $name);
            $name = $explodePath[count($explodePath) - 1];
            unset($explodePath[count($explodePath) - 1]);
            $path = implode('/', $explodePath);
        }

        $filePath = !$template
            ? (isset($path)
                ? "app/Http/Controllers/{$path}/{$name}.php"
                : "app/Http/Controllers/{$name}.php")
            : "page-templates/{$name}.php";

        // If a Controller already exists
        if (file_exists("{$this->app->basePath()}/{$filePath}")) {
            $output->writeln("<error>Controller {$name} already exists.</error>");

            return Command::FAILURE;
        }

        $stub = !$template ? file_get_contents(__DIR__ . '/stubs/Controller.stub') : file_get_contents(__DIR__ . '/stubs/Controller.template.stub');
        $stub = str_replace('DummyController', !$template ? $name : "{$name}Controller", $stub);
        if ($template) {
            $viewName = Str::snake($name, '-');
            $viewPath = "templates/{$viewName}.twig";

            $stub = str_replace(
                '{%TEMPLATE_NAME%}',
                is_string($template) ? $template : ucwords(str_replace('-', ' ', Str::kebab(str_replace('Controller', '', $name)))),
                $stub,
            );
            $stub = str_replace(
                '{{ controller_view_path }}',
                $viewPath,
                $stub
            );
        }

        if (isset($path)) {
            $path = str_replace('/', '\\', $path);

            // Replace the namespace
            $stub = str_replace('namespace App\Http\Controllers;', "namespace App\Http\Controllers\\{$path};", $stub);

            // Add an use statement for the Controller extending
            $stub = str_replace('{%USE_STATEMENTS%}', 'use App\Http\Controllers\Controller;' . PHP_EOL . '{%USE_STATEMENTS%}', $stub);
        }

        // Remove the placeholder for use statements
        $stub = str_replace("\n{%USE_STATEMENTS%}", '', $stub);

        $this->createFile($filePath, $stub);

        if ($template) {
            $this->createFile("resources/views/{$viewPath}", file_get_contents(__DIR__ . '/stubs/Controller.view.stub'));
        }

        if (!$template) {
            $output->writeln($this->summary($name, $filePath, 'Controller'));

            return Command::SUCCESS;
        }

        $output->writeln(
            $this->summary($name, [
                $filePath,
                "resources/views/{$viewPath}",
            ], 'Controller')
        );

        return Command::SUCCESS;
    }
}
