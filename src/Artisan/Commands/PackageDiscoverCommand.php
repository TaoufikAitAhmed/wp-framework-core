<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use themes\Wordpress\Framework\Core\PackageManifest;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageDiscoverCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected string $signature = 'package:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Rebuild the cached package manifest';

    /**
     * Execute the console command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @SuppressWarnings(PHPMD)
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var PackageManifest $manifest */
        $manifest = $this->app->get(PackageManifest::class);

        if (is_file($manifest->manifestPath)) {
            unlink($manifest->manifestPath);
        }

        $manifest->build();

        if (!is_array($manifest->manifest) || count($manifest->manifest) === 0) {
            $output->writeln('<info>No packages to discover.</info>');

            return Command::SUCCESS;
        }

        foreach (array_keys($manifest->manifest) as $package) {
            $output->writeln("Discovered Package: <info>{$package}</info>");
        }

        $output->writeln('<info>Package manifest generated successfully.</info>');

        return Command::SUCCESS;
    }
}
