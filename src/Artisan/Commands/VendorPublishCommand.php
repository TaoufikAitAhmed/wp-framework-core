<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use themes\Wordpress\Framework\Core\Providers\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\MountManager;
use Rareloop\Lumberjack\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VendorPublishCommand extends MakeFromStubCommand
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * The provider to publish.
     *
     * @var string|null
     */
    protected ?string $provider = null;

    /**
     * The tags to publish.
     *
     * @var array
     */
    protected array $tags = [];

    /**
     * The console command signature.
     *
     * @var string
     */
    protected string $signature = 'vendor:publish
                    {--force : Overwrite any existing files}
                    {--all : Publish assets for all service providers without prompt}
                    {--provider= : The service provider that has assets you want to publish}
                    {--tag=* : One or many tags that have assets you want to publish}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Publish any publishable assets from vendor packages';

    /**
     * Create a new command instance.
     *
     * @param Application $app
     * @param Filesystem  $filesystem
     */
    public function __construct(Application $app, Filesystem $filesystem)
    {
        parent::__construct($app);
        $this->files = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $this->determineWhatShouldBePublished();

        foreach ($this->tags ?: [null] as $tag) {
            $this->publishTag($tag);
        }

        $this->info('Publishing complete.');

        return Command::SUCCESS;
    }

    /**
     * Determine the provider or tag(s) to publish.
     *
     * @return void
     */
    protected function determineWhatShouldBePublished()
    {
        if ($this->option('all')) {
            return;
        }

        [$this->provider, $this->tags] = [str_replace('\\\\', '\\', $this->option('provider')), (array) $this->option('tag')];

        if (!$this->provider && !$this->tags) {
            $this->promptForProviderOrTag();
        }
    }

    /**
     * Prompt for which provider or tag to publish.
     *
     * @return void
     */
    protected function promptForProviderOrTag()
    {
        $choice = $this->choice("Which provider or tag's files would you like to publish?", $choices = $this->publishableChoices());

        if ($choice == $choices[0] || is_null($choice)) {
            return;
        }

        $this->parseChoice($choice);
    }

    /**
     * The choices available via the prompt.
     *
     * @return array
     */
    protected function publishableChoices(): array
    {
        return array_merge(
            ['<comment>Publish files from all providers and tags listed below</comment>'],
            preg_filter('/^/', '<comment>Provider: </comment>', Arr::sort(ServiceProvider::publishableProviders())),
            preg_filter('/^/', '<comment>Tag: </comment>', Arr::sort(ServiceProvider::publishableGroups())),
        );
    }

    /**
     * Parse the answer that was given via the prompt.
     *
     * @param string $choice
     *
     * @return void
     */
    protected function parseChoice(string $choice)
    {
        [$type, $value] = explode(': ', strip_tags($choice));

        if ($type === 'Provider') {
            $this->provider = $value;
        } elseif ($type === 'Tag') {
            $this->tags = [$value];
        }
    }

    /**
     * Publishes the assets for a tag.
     *
     * @param string|null $tag
     *
     * @return void
     * @throws FilesystemException
     */
    protected function publishTag(?string $tag)
    {
        $published = false;

        $pathsToPublish = $this->pathsToPublish($tag);

        foreach ($pathsToPublish as $from => $to) {
            $this->publishItem($from, $to);

            $published = true;
        }

        if ($published === false) {
            $this->comment(sprintf('No publishable resources for tag [%s].', $tag));
        }
    }

    /**
     * Get all of the paths to publish.
     *
     * @param string|null $tag
     *
     * @return array
     */
    protected function pathsToPublish(?string $tag): array
    {
        return ServiceProvider::pathsToPublish($this->provider, $tag);
    }

    /**
     * Publish the given item from and to the given location.
     *
     * @param string $from
     * @param string $to
     *
     * @return void
     * @throws FilesystemException
     */
    protected function publishItem(string $from, string $to)
    {
        if ($this->files->isFile($from)) {
            $this->publishFile($from, $to);
        } elseif ($this->files->isDirectory($from)) {
            $this->publishDirectory($from, $to);
        } else {
            $this->error("Can't locate path: <{$from}>");
        }
    }

    /**
     * Publish the file to the given path.
     *
     * @param string $from
     * @param string $to
     *
     * @return void
     */
    protected function publishFile(string $from, string $to)
    {
        if (!$this->option('force') && $this->files->exists($to)) {
            $this->error("The file '{$to}' already exists.");

            return;
        }

        $this->createParentDirectory(dirname($to));

        $this->files->copy($from, $to);

        $this->status($from, $to, 'File');
    }

    /**
     * Publish the directory to the given directory.
     *
     * @param string $from
     * @param string $to
     *
     * @return void
     * @throws FilesystemException
     */
    protected function publishDirectory(string $from, string $to)
    {
        $this->moveManagedFiles(
            new MountManager([
                'from' => new Flysystem(new LocalAdapter($from)),
                'to'   => new Flysystem(new LocalAdapter($to)),
            ]),
        );

        $this->status($from, $to, 'Directory');
    }

    /**
     * Move all the files in the given MountManager.
     *
     * @param MountManager $manager
     *
     * @return void
     * @throws FilesystemException
     */
    protected function moveManagedFiles(MountManager $manager)
    {
        foreach ($manager->listContents('from://', true) as $file) {
            if ($file['type'] === 'file' && (!$manager->has('to://' . $file['path']) || $this->option('force'))) {
                $manager->put('to://' . $file['path'], $manager->read('from://' . $file['path']));
            }
        }
    }

    /**
     * Create the directory to house the published files if needed.
     *
     * @param string $directory
     *
     * @return void
     */
    protected function createParentDirectory(string $directory)
    {
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    /**
     * Write a status message to the console.
     *
     * @param string $from
     * @param string $to
     * @param string $type
     *
     * @return void
     */
    protected function status($from, $to, $type)
    {
        $from = str_replace(base_path(), '', realpath($from));

        $to = str_replace(base_path(), '', realpath($to));

        $this->info(sprintf('<info>Copied %s</info> <comment>[%s]</comment> <info>To</info> <comment>[%s]</comment>', $type, $from, $to));
    }
}
