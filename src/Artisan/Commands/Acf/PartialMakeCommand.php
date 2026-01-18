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
class PartialMakeCommand extends MakeFromStubCommand
{
    protected string $signature = 'acf:partial {name : The class name of the Partial}';

    protected string $description = 'Create a new ACF field group partial.';

/*************  ✨ Windsurf Command ⭐  *************/
        /**
         * Execute the console command.
         *
         * @param InputInterface  $input
         * @param OutputInterface $output
         *
         * @return int
         */
/*******  4476fc68-6056-4a6f-b7d7-28fc0aef70f1  *******/    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        // If it has sub folder
        if (Str::contains($name, '/')) {
            $explodePath = explode('/', $name);
            $name = $explodePath[count($explodePath) - 1];
            unset($explodePath[count($explodePath) - 1]);
            $path = implode('/', $explodePath);
        }

        $filePath = isset($path)
            ? "app/Acf/Partials/{$path}/{$name}.php"
            : "app/Acf/Partials/{$name}.php";

        // If an Acf Partial already exists
        if (file_exists("{$this->app->basePath()}/{$filePath}")) {
            $output->writeln("<error>ACF Partial {$name} already exists.</error>");
            return Command::FAILURE;
        }

        $stub = file_get_contents(__DIR__ . '/stubs/Partial.stub');
        $stub = str_replace('DummyClass', $name, $stub);
        $stub = str_replace('DummySnake', Str::snake($name), $stub);
        $stub = str_replace('dummyCamel', Str::camel($name), $stub);

        if (isset($path)) {
            $path = str_replace('/', '\\', $path);
            // Replace the namespace
            $stub = str_replace(
                'namespace App\Acf\Partials;',
                "namespace App\Acf\Partials\\{$path};",
                $stub
            );
        }

        $this->createFile($filePath, $stub);

        // --- Twig file creation ---
        $twigPath = isset($path)
            ? "resources/views/components/{$path}"
            : "resources/views/components";
        $twigFileName = Str::kebab($name) . '.twig'; // Use kebab-case (hyphens)
        $twigFile = $twigPath . '/' . $twigFileName;

        // Ensure directory exists
        if (!is_dir($this->app->basePath() . '/' . $twigPath)) {
            mkdir($this->app->basePath() . '/' . $twigPath, 0755, true);
        }

        // Basic twig stub content, you can customize this
        $twigStub = "{# {$name} partial #}\n<div class=\"acf-partial acf-partial--" . Str::kebab($name) . "\">\n    {# Content here #}\n</div>\n";

        // Only create if not exists
        $twigFullPath = $this->app->basePath() . '/' . $twigFile;
        if (file_exists($twigFullPath)) {
            $output->writeln("<comment>Twig partial already exists:</comment> {$twigFile}");
        }

        // Show both PHP and Twig file paths in the summary
        $outputPath = [
            $filePath,
            $twigFile
        ];
        
        $output->writeln($this->summary($name, $outputPath, 'ACF Partial'));

        return Command::SUCCESS;
    }
}
