<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use ICanBoogie\Inflector;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class TaxonomyMakeCommand extends MakeFromStubCommand
{
    protected string $signature = 'make:taxonomy {name : The class name of the Taxonomy (singular)}';

    protected string $description = 'Create a Taxonomy';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $singular = $input->getArgument('name');

        // If it have sub folder
        if (Str::contains($singular, '/')) {
            $explodePath = explode('/', $singular);
            $singular = $explodePath[count($explodePath) - 1];
            unset($explodePath[count($explodePath) - 1]);
            $path = implode('/', $explodePath);
        }

        $filePath = isset($path) ? "app/Taxonomies/{$path}/{$singular}.php" : "app/Taxonomies/{$singular}.php";

        // If a Taxonomy already exists
        if (file_exists("{$this->app->basePath()}/{$filePath}")) {
            $output->writeln("<error>Taxonomy {$singular} already exists.</error>");

            return Command::FAILURE;
        }

        $taxonomyName = Str::snake($singular, '_');
        $taxonomySlugified = Str::snake($singular, '-');
        $plural = Inflector::get('en')->pluralize($singular);

        $helper = new QuestionHelper();

        $question = new Question("<info>Plural</info> [default: {$plural}] ", $plural);
        $plural = $helper->ask($input, $output, $question);

        $question = new Question("<info>WordPress Taxonomy Name</info> [default: {$taxonomyName}] ", $taxonomyName);
        $taxonomyName = $helper->ask($input, $output, $question);

        $question = new Question("<info>Slug</info> [default: {$taxonomySlugified}] ", $taxonomySlugified);
        $slug = $helper->ask($input, $output, $question);

        $question = new ConfirmationQuestion('<info>Register Taxonomy from Config? (y/n)</info> [default: y] ');
        $registerTaxonomy = $helper->ask($input, $output, $question);

        $stub = file_get_contents(__DIR__ . '/stubs/Taxonomy.stub');
        $stub = str_replace('DummyTaxonomy', $singular, $stub);
        $stub = str_replace('dummy-taxonomy-name', $taxonomyName, $stub);
        $stub = str_replace('dummy-slug', $slug, $stub);
        $stub = str_replace('DummyPlural', Str::ucfirst(Str::snake($plural, ' ')), $stub);
        $stub = str_replace('DummySingular', Str::ucfirst(Str::snake($singular, ' ')), $stub);

        if (isset($path)) {
            $path = str_replace('/', '\\', $path);

            // Replace the namespace
            $stub = str_replace('namespace App\Taxonomies;', "namespace App\Taxonomies\\{$path};", $stub);
        }

        $this->createFile($filePath, $stub);

        if ($registerTaxonomy) {
            $this->registerTaxonomyInConfig(isset($path) ? "{$path}\\{$singular}" : $singular);
        }

        $output->writeln($this->summary($singular, !$registerTaxonomy ? $filePath : [$filePath, 'config/taxonomies.php'], 'Taxonomy'));

        return Command::SUCCESS;
    }

    protected function registerTaxonomyInConfig($singular)
    {
        $configPath = "{$this->app->basePath()}/config/taxonomies.php";
        $config = file_get_contents($configPath);
        $config = str_replace("'register' => [", "'register' => [\n\t\tApp\Taxonomies\\{$singular}::class,", $config);
        file_put_contents($configPath, $config);
    }
}
