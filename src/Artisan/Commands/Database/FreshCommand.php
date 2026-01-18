<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands\Database;

use themes\Wordpress\Framework\Core\Artisan\Commands\Command;
use themes\Wordpress\Framework\Core\Artisan\Concerns\ConfirmableTrait;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WP_Query;
use WP_Term_Query;
use WP_User_Query;

class FreshCommand extends Command
{
    use ConfirmableTrait;

    protected string $signature = 'db:fresh {--seed : Indicates if the seed task should be re-run.}';

    protected string $description = 'Drop all fake records.';

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $this->info('Dropping all fakes records...');

        $this->deletePosts();
        $this->deleteTerms();
        $this->deleteUsers();

        if ($this->option('seed')) {
            $this->info('Running seeders...');
            $this->call('db:seed');
        }

        $this->comment('All done!');

        return Command::SUCCESS;
    }

    /**
     * Delete all terms.
     *
     * @return void
     */
    protected function deleteTerms(): void
    {
        $fakeRecords = new WP_Term_Query([
            'meta_query' => [
                [
                    'key'   => '_wp-framework-core-fake',
                    'value' => true,
                ],
            ],
            'taxonomy'   => array_keys(get_taxonomies()),
            'hide_empty' => false,
        ]);

        if ($fakeRecords->get_terms()) {
            foreach ($fakeRecords->get_terms() as $fakeRecord) {
                wp_delete_term($fakeRecord->term_id, $fakeRecord->taxonomy);
            }
        }
    }

    /**
     * Delete all users.
     *
     * @return void
     */
    protected function deleteUsers(): void
    {
        $fakeRecords = new WP_User_Query([
            'meta_query' => [
                [
                    'key'   => '_wp-framework-core-fake',
                    'value' => true,
                ],
            ],
        ]);

        if ($fakeRecords->get_results()) {
            foreach ($fakeRecords->get_results() as $fakeRecord) {
                wp_delete_user($fakeRecord->ID);
            }
        }
    }

    /**
     * Delete all posts.
     *
     * @return void
     */
    protected function deletePosts(): void
    {
        $fakeRecords = new WP_Query([
            'meta_query'     => [
                [
                    'key'   => '_wp-framework-core-fake',
                    'value' => true,
                ],
            ],
            'post_type'      => array_keys(get_post_types()),
            'post_status'    => 'any',
            'posts_per_page' => -1,
        ]);

        if ($fakeRecords->have_posts()) {
            foreach ($fakeRecords->get_posts() as $fakeRecord) {
                wp_delete_post($fakeRecord->ID, true);
            }
        }
    }
}
