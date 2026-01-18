<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Providers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Config;
use themes\Wordpress\Framework\Core\Providers\ThemeSetupServiceProvider;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use InvalidArgumentException;
use Mockery;
use phpmock\MockBuilder;

/**
 * @preserveGlobalState disabled
 */
class ThemeSetupServiceProviderTest extends WordpressTestCase
{
    public function testItCanDisableGutenberg()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', ['disable_gutenberg' => true]);

        Filters\expectAdded('use_block_editor_for_post')
            ->once()
            ->with('__return_false');

        Actions\expectAdded('init')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        Functions\expect('wp_dequeue_style')
            ->once()
            ->with('wp-block-library');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    public function testItCanLoadThemeTextDomain()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', [
            'text_domain' => [
                'domain' => 'foo',
                'path'   => '/languages',
            ],
        ]);

        Functions\expect('load_theme_textdomain')
            ->once()
            ->with('foo', 'https://example.com/app/themes/theme/languages');

        Actions\expectAdded('after_setup_theme')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    public function testItThrowsAnExceptionIfTheDomainForThemeTextDomainIsMissing()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', [
            'text_domain' => [
                'path' => '/languages',
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You need to set a domain to add a theme text domain.');

        Actions\expectAdded('after_setup_theme')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    public function testItThrowsAnExceptionIfThePathForThemeTextDomainIsMissing()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', [
            'text_domain' => [
                'domain' => 'foo',
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You need to set a path to add a theme text domain.');

        Actions\expectAdded('after_setup_theme')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    public function testItCanRemoveEmojis()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', [
            'remove_emojis' => true,
        ]);

        Actions\expectRemoved('wp_head')
            ->once()
            ->with('print_emoji_detection_script', 7);
        Actions\expectRemoved('admin_print_scripts')
            ->once()
            ->with('print_emoji_detection_script');
        Actions\expectRemoved('wp_print_styles')
            ->once()
            ->with('print_emoji_styles');
        Actions\expectRemoved('admin_print_styles')
            ->once()
            ->with('print_emoji_styles');

        Filters\ExpectRemoved('the_content_feed')
            ->once()
            ->with('wp_staticize_emoji');
        Filters\ExpectRemoved('comment_text_rss')
            ->once()
            ->with('wp_staticize_emoji');
        Filters\expectRemoved('wp_mail')
            ->once()
            ->with('wp_staticize_emoji_for_email');

        Filters\expectAdded('tiny_mce_plugins')->once();
        Filters\expectAdded('wp_resource_hints')->once();

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    public function testItCanRemoveWpEmbedScript()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', ['disable_wp_embed' => true]);

        Functions\expect('wp_deregister_script')
            ->once()
            ->with('wp-embed');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    public function testItDontEnqueueCommentScriptOnTerm()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', ['automatic_comments_script' => true]);

        $wpTerm = Mockery::mock('\WP_Term');

        Functions\expect('get_queried_object')
            ->once()
            ->andReturn($wpTerm);

        Functions\expect('wp_enqueue_script')
            ->never()
            ->with('comment-reply');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    public function testItDequeueCommentsScriptsWhenCommentsAreNotOpen()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', ['automatic_comments_script' => true]);

        $wpPost = Mockery::mock('\WP_Post');

        Functions\expect('get_queried_object')
            ->once()
            ->andReturn($wpPost);

        Functions\expect('comments_open')
            ->once()
            ->andReturn(false);

        Functions\expect('wp_enqueue_script')
            ->never()
            ->with('comment-reply');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    public function testItDequeueCommentsScriptsWhenCommentsAreOpen()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', ['automatic_comments_script' => true]);

        $wpPost = Mockery::mock('\WP_Post');

        Functions\expect('get_queried_object')
            ->once()
            ->andReturn($wpPost);

        Functions\expect('comments_open')
            ->once()
            ->andReturn(true);

        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('comment-reply');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    public function testItCanRequireAuthorNameInComments()
    {
        $this->mockWordPressLanguageFunctions();
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', [
            'comments' => [
                'require_author_name' => [
                    'enable' => true,
                ],
            ],
        ]);

        Functions\when('get_site_url')
            ->justReturn('http://example.com/');

        Functions\expect('update_option')
            ->with('require_name_email', '0');

        Functions\expect('wp_die')
            ->once()
            ->with('You need to fill your name.');

        Filters\expectAdded('preprocess_comment')
            ->once()
            ->whenHappen(fn ($callback) => $callback(['comment_author' => '']));

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    public function testItCanRequireAuthorNameInCommentsAndChangeTheMessage()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', [
            'comments' => [
                'require_author_name' => [
                    'enable'  => true,
                    'message' => 'foo',
                ],
            ],
        ]);

        Functions\when('get_site_url')
            ->justReturn('http://example.com/');

        Functions\expect('update_option')
            ->with('require_name_email', '0');

        Functions\expect('wp_die')
            ->once()
            ->with('foo');

        Filters\expectAdded('preprocess_comment')
            ->once()
            ->whenHappen(fn ($callback) => $callback(['comment_author' => '']));

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    public function testItCanRemoveAuthorIpFromComments()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('theme', [
            'comments' => [
                'remove_author_ip' => true,
            ],
        ]);

        Filters\expectAdded('pre_comment_user_ip')->once();

        $provider = new ThemeSetupServiceProvider($app);
        $provider->boot($config);
    }

    /**
     * Handle `get_stylesheet_directory_uri` function to return
     * https://example.com
     */
    protected function handleGetStylesheetDirectoryUri()
    {
        Functions\when('get_stylesheet_directory_uri')->justReturn('https://example.com/app/themes/theme');
    }

    protected function mockWordPressLanguageFunctions(string $namespace = 'themes\Wordpress\Framework\Core\Providers')
    {
        $builder = new MockBuilder();
        $builder
            ->setNamespace($namespace)
            ->setName('__')
            ->setFunction(fn ($input) => $input);

        $mock = $builder->build();
        $mock->enable();
    }
}
