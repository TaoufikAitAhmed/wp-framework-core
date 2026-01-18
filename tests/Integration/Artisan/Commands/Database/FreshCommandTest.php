<?php

namespace themes\Wordpress\Framework\Core\Test\Integration;

use themes\Wordpress\Framework\Core\Database\Factories\PageFactory;
use themes\Wordpress\Framework\Core\Database\Factories\PostFactory;
use themes\Wordpress\Framework\Core\Database\Factories\TermFactory;
use themes\Wordpress\Framework\Core\Database\Factories\UserFactory;
use themes\Wordpress\Framework\Core\Term;
use Rareloop\Lumberjack\Page;
use Rareloop\Lumberjack\Post;
use Timber\Image;
use Timber\User;

/**
 * @preserveGlobalState disabled
 * @runTestsInSeparateProcesses
 */
class FreshCommandTest extends IntegrationTestCase
{
    public function test_it_can_wipe_all_fake_data_from_database()
    {
        $termsFactory = TermFactory::new()->times(5)->make();
        $postsFactory = PostFactory::new()->times(5)->make();
        $usersFactory = UserFactory::new()->times(5)->make();
        $pagesFactory = PageFactory::new()->times(5)->make();

        $termsWp = $this->factory()->term->create_many(5);
        $postsWp = $this->factory()->post->create_many(5);
        $usersWp = $this->factory()->user->create_many(5);
        $pagesWp = $this->factory()->post->create_many(5, [
            'post_type' => 'page',
        ]);
        $attachmentsWp = $this->factory()->attachment->create_many(5);

        $this->assertCount(
            11, // 11 because there is the uncategorized term
            get_terms([
                'hide_empty' => false,
            ])
        );
        $this->assertCount(10, Post::all());
        $this->assertCount(
            11, // 11 because there is a default user
            get_users()
        );
        $this->assertCount(10, Page::all());
        $this->assertCount(
            10,
            get_posts([
                'post_type'   => 'attachment',
                'numberposts' => -1,
                'post_status' => null,
                'post_parent' => null,
            ])
        );

        call_artisan_command('db:fresh');

        $this->assertCount(
            6, // 6 because there is a default term
            get_terms([
                'hide_empty' => false,
            ])
        );
        $this->assertCount(5, Post::all());
        $this->assertCount(
            6, // 6 because there is a default user
            get_users()
        );
        $this->assertCount(5, Page::all());
        $this->assertCount(
            5,
            get_posts([
                'post_type'   => 'attachment',
                'numberposts' => -1,
                'post_status' => null,
                'post_parent' => null,
            ])
        );

        // Check terms generated with factory doesn't exist anymore.
        collect($termsFactory)->map(function (Term $termFactory) {
            $this->assertNull(term_exists($termFactory->ID, $termFactory->taxonomy));
        });

        // Check posts generated with factory doesn't exist anymore.
        collect($postsFactory)->map(function (Post $postFactory) {
            $this->assertFalse(get_post_status(get_post($postFactory->ID)));
        });

        // Check users generated with factory doesn't exist anymore.
        collect($usersFactory)->map(function (User $user) {
            $this->assertFalse((bool) get_users(['include' => $user->id, 'fields' => 'ID']));
        });

        // Check pages generated with factory doesn't exist anymore.
        collect($pagesFactory)->map(function (Page $pageFactory) {
            $this->assertFalse(get_post_status(get_post($pageFactory->ID)));
        });

        // Check attachments generated with post factory doesn't exist anymore.
        collect($postsFactory)->map(function (Post $factory) {
            $this->assertNotInstanceOf(Image::class, $factory->thumbnail());
        });

        // Check all things generated without factory still exists.
        collect([$termsWp, $postsWp, $usersWp, $pagesWp, $attachmentsWp])->map(function (array $factories) {
            collect($factories)->map(function ($factory) {
                $this->assertNotNull($factory);
            });
        });
    }

    public function test_it_can_wipe_all_fake_data_from_database_and_seed_again()
    {
        mkdir(dirname(__DIR__, 4) . '/theme/database');
        mkdir(dirname(__DIR__, 4) . '/theme/database/seeders');
        touch(dirname(__DIR__, 4) . '/theme/database/seeders/DatabaseSeeder.php');
        file_put_contents(
            dirname(__DIR__, 4) . '/theme/database/seeders/DatabaseSeeder.php',
            <<<'PHP'
                <?php

                namespace Database\Seeders;

                use themes\Wordpress\Framework\Core\Database\Seeder;

                class DatabaseSeeder extends Seeder
                {
                    /**
                     * Run the database seeds.
                     *
                     * @return void
                     * @throws InvocationException
                     * @throws NotCallableException
                     * @throws NotEnoughParametersException
                     */
                    public function run(): void
                    {
                        wp_insert_post([
                          'post_title'    => wp_strip_all_tags('mon super titre'),
                          'post_content'  => 'mon super contenu',
                          'post_status'   => 'publish',
                          'post_type' => 'page',
                          'post_author'   => 1,
                        ]);
                    }
                }
                PHP
        );

        $post = PostFactory::new()->make();

        call_artisan_command('db:fresh', [
            '--seed' => true,
        ]);

        // Check the generated post doesn't exist
        $this->assertFalse(get_post_status(get_post($post->ID)));

        // Check the DatabaseSeeder has been run.
        $this->assertNotNull(get_page_by_title('mon super titre'));
    }
}
