<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginMakeCommand extends MakeFromStubCommand
{
    protected string $signature = 'make:plugin {name : The class name of the Plugin}';

    protected string $description = 'Create a Plugin';

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

        $filePath = isset($path) ? "app/Plugins/{$path}/{$name}.php" : "app/Plugins/{$name}.php";

        // If a Plugin already exists
        if (file_exists("{$this->app->basePath()}/{$filePath}")) {
            $output->writeln("<error>Plugin {$name} already exists.</error>");

            return Command::FAILURE;
        }

        $registerPlugin = $this->confirm('<info>Do you want to clean this plugin by default ?</info>', true);

        $stub = file_get_contents(__DIR__ . '/stubs/Plugin.stub');
        $stub = str_replace('DummyClassName', $name, $stub);

        if (isset($path)) {
            $path = str_replace('/', '\\', $path);

            // Replace the namespace
            $stub = str_replace('namespace App\Plugins;', "namespace App\Plugins\\{$path};", $stub);
        }

        $this->createFile($filePath, $stub);

        if ($registerPlugin) {
            $this->registerPluginInConfig(isset($path) ? "{$path}\\{$name}" : $name);
        }

        $output->writeln($this->summary($name, !$registerPlugin ? $filePath : [$filePath, 'config/plugins.php'], 'Plugin'));

        return Command::SUCCESS;
    }

    protected function registerPluginInConfig($name)
    {
        $configPath = "{$this->app->basePath()}/config/plugins.php";
        $config = file_get_contents($configPath);
        $config = str_replace('[', "[\n\t\tApp\Plugins\\{$name}::class,", $config);
        file_put_contents($configPath, $config);
    }
}
