<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use ICanBoogie\Inflector;
use Illuminate\Support\Str;
use function Stringy\create as s;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class PostTypeMakeCommand extends MakeFromStubCommand
{
    protected string $signature = 'make:posttype {name : The class name of the PostType (singular)}';

    protected string $description = 'Create a PostType';

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

        $filePath = isset($path) ? "app/PostTypes/{$path}/{$singular}.php" : "app/PostTypes/{$singular}.php";

        // If a Post Type already exists
        if (file_exists("{$this->app->basePath()}/{$filePath}")) {
            $output->writeln("<error>Post Type {$singular} already exists.</error>");

            return Command::FAILURE;
        }

        $postTypeName = Str::snake($singular, '_');
        $postTypeSlugified = Str::snake($singular, '-');
        $plural = Inflector::get('en')->pluralize($singular);

        $helper = new QuestionHelper();

        $question = new Question("<info>Plural</info> [default: {$plural}] ", $plural);
        $plural = $helper->ask($input, $output, $question);

        $question = new Question("<info>WordPress Post Name</info> [default: {$postTypeName}] ", $postTypeName);
        $postTypeName = $helper->ask($input, $output, $question);

        $question = new Question("<info>Slug</info> [default: {$postTypeSlugified}] ", $postTypeSlugified);
        $slug = $helper->ask($input, $output, $question);

        $features = ['Content Editor', 'Featured Images', 'Revisions', 'Archives'];
        $question = new ChoiceQuestion('<info>Which features do you want?</info> [default: 0,1,2,3]', $features, '0,1,2,3');
        $question->setMultiselect(true);
        $featuresSelected = $helper->ask($input, $output, $question);

        $question = new ConfirmationQuestion('<info>Register PostType from Config? (y/n)</info> [default: y] ');
        $registerPostType = $helper->ask($input, $output, $question);

        $stub = file_get_contents(__DIR__ . '/stubs/PostType.stub');
        $stub = str_replace('DummyPostType', $singular, $stub);
        $stub = str_replace('dummy-post-name', $postTypeName, $stub);
        $stub = str_replace('dummy-slug', $slug, $stub);
        $stub = str_replace('DummyPlural', Str::ucfirst(Str::snake($plural, ' ')), $stub);
        $stub = str_replace('DummySingular', Str::ucfirst(Str::snake($singular, ' ')), $stub);

        if (!in_array('Archives', $featuresSelected)) {
            $stub = str_replace("'has_archive' => true,", "'has_archive' => false,", $stub);
        }

        if (!in_array('Content Editor', $featuresSelected)) {
            $stub = str_replace("'editor',\n", '', $stub);
        }

        if (!in_array('Revisions', $featuresSelected)) {
            $stub = str_replace("'revisions',\n", '', $stub);
        }

        if (!in_array('Featured Images', $featuresSelected)) {
            $stub = str_replace("'thumbnail',\n", '', $stub);
        }

        if (isset($path)) {
            $path = str_replace('/', '\\', $path);

            // Replace the namespace
            $stub = str_replace('namespace App\PostTypes;', "namespace App\PostTypes\\{$path};", $stub);
        }

        $this->createFile($filePath, $stub);

        if ($registerPostType) {
            $this->registerPostTypeInConfig(isset($path) ? "{$path}\\{$singular}" : $singular);
        }

        $output->writeln($this->summary($singular, $filePath, 'Post Type'));

        return Command::SUCCESS;
    }

    protected function registerPostTypeInConfig($singular)
    {
        $configPath = "{$this->app->basePath()}/config/posttypes.php";
        $config = file_get_contents($configPath);
        $config = str_replace("'register' => [", "'register' => [\n\t\tApp\PostTypes\\{$singular}::class,", $config);
        file_put_contents($configPath, $config);
    }
}
