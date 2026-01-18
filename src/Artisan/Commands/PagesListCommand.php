<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WP_Query;

class PagesListCommand extends Command
{
    protected string $signature = 'pages:list';

    protected string $description = 'List all pages.';

    /**
     * Execute the console command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @SuppressWarnings(PHPMD)
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pages = new Collection([
            $this->getPagesLinks(),
            $this->getSearchPageLink(),
            $this->getErrorPageLink(),
            $this->getCategoriesLinks(),
            $this->getPostsLinksWithoutFake(),
            $this->getCustomPostTypesLinks(),
            $this->getCustomPostTypesArchivesLinks(),
            $this->getCustomTaxonomiesLinks(),
        ]);

        $pages->map(static function ($page) use ($output): void {
            $output->writeln($page);
        });

        $output->writeln('');
        $output->writeln($this->summary());

        return Command::SUCCESS;
    }
    // phpcs: enable

    /**
     * Return the block creation summary.
     *
     * @return array
     */
    protected function summary(): array
    {
        return ['🎉 <fg=blue;options=bold>Pages</> were successfully listed.', '     ⮑  <fg=blue>You can copy links higher.</>'];
    }

    /**
     * Get all pages links
     *
     * @return array
     */
    private function getPagesLinks(): array
    {
        $pagesLinks = [];

        foreach (get_pages() as $page) {
            $pagesLinks[] = get_permalink($page);
        }

        return $pagesLinks;
    }

    /**
     * Get search page link
     *
     * @return string
     */
    private function getSearchPageLink(): string
    {
        return get_home_url(null, '?s=+');
    }

    /**
     * Get error page link
     *
     * @return string
     */
    private function getErrorPageLink(): string
    {
        return get_home_url(null, '404');
    }

    /**
     * Get categories links
     *
     * @return array
     */
    private function getCategoriesLinks(): array
    {
        $categoriesLinks = [];

        foreach (get_categories() as $category) {
            $categoriesLinks[] = get_category_link($category);
        }

        return $categoriesLinks;
    }

    /**
     * Get posts links without faked content
     *
     * @return array
     */
    private function getPostsLinksWithoutFake(): array
    {
        $postsLinksWithoutFake = [];

        foreach (
            get_posts([
                'meta_query' => [
                    'relation' => 'OR',
                    [
                        'key' => '_fake',
                        'compare' => 'NOT EXISTS',
                    ],
                ],
            ])
            as $postNotFake
        ) {
            $postsLinksWithoutFake[] = get_permalink($postNotFake);
        }

        return $postsLinksWithoutFake;
    }

    /**
     * Get custom post types links
     *
     * @return array
     */
    private function getCustomPostTypesLinks(): array
    {
        $customPostTypesLinks = [];

        foreach (
            get_post_types(
                [
                    'public' => true,
                    '_builtin' => false,
                ],
                'object',
            )
            as $customPostType
        ) {
            $posts = new WP_Query([
                'post_type' => $customPostType->name,
                'post_status' => 'publish',
                'posts_per_page' => -1,
            ]);

            if ($posts->have_posts()) {
                foreach ($posts->get_posts() as $post) {
                    $customPostTypesLinks[] = get_permalink($post);
                }
            }
        }

        return $customPostTypesLinks;
    }

    /**
     * Get custom post types archives links
     *
     * @return array
     */
    private function getCustomPostTypesArchivesLinks(): array
    {
        $customPostTypesArchivesLinks = [];

        foreach (
            get_post_types(
                [
                    'public' => true,
                    '_builtin' => false,
                ],
                'object',
            )
            as $customPostType
        ) {
            $archiveLink = get_post_type_archive_link($customPostType->name);
            if ($archiveLink) {
                $customPostTypesArchivesLinks[] = $archiveLink;
            }
        }

        return $customPostTypesArchivesLinks;
    }

    /**
     * Get custom taxonomies links
     *
     * @return array
     */
    private function getCustomTaxonomiesLinks(): array
    {
        $customTaxonomiesLinks = [];

        foreach (
            get_taxonomies(
                [
                    'public' => true,
                    '_builtin' => false,
                ],
                'object',
            )
            as $customTaxonomy
        ) {
            $tags = get_tags([
                'taxonomy' => $customTaxonomy->name,
                'hide_empty' => true,
            ]);

            if (is_array($tags) && $tags) {
                foreach ($tags as $tag) {
                    $customTaxonomiesLinks[] = get_term_link($tag);
                }
            }
        }

        return $customTaxonomiesLinks;
    }
}
