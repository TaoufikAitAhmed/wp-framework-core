<?php

namespace themes\Wordpress\Framework\Core\Providers;

use themes\Wordpress\Framework\Core\Admin\Notices\Notice;
use themes\Wordpress\Framework\Core\Admin\Notices\Types\Info;
use themes\Wordpress\Framework\Core\Config;
use InvalidArgumentException;

class ThemeSetupServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @param Config $config
     *
     * @return void
     * @throws \ReflectionException
     */
    public function boot(Config $config)
    {
        $theme = $config->get('theme');

        if (isset($theme['comments']['require_author_name']['enable']) && $theme['comments']['require_author_name']['enable']) {
            add_filter('preprocess_comment', function ($fields) use ($theme) {
                if ($fields['comment_author'] === '') {
                    wp_die(
                        isset($theme['comments']['require_author_name']['message'])
                            ? $theme['comments']['require_author_name']['message']
                            : __('You need to fill your name.'),
                    );
                }

                return $fields;
            });

            // Update the back office so it doesn't conflicts
            update_option('require_name_email', '0');
            $optionNotice = new Notice(
                __(
                    sprintf(
                        'The <strong>Comment author must fill out name and email</strong> option has been <strong>disabled</strong> in the <a href="%s/wp-admin/options-discussion.php">Comments settings</a> page since it is not compatible with <strong>require_author_name</strong> in <strong>config/theme.php</strong>.',
                        get_site_url()
                    ),
                    'wp-framework-core'
                ),
                Info::class
            );
            $optionNotice->render();
        }

        if (isset($theme['comments']['remove_author_ip']) && $theme['comments']['remove_author_ip']) {
            add_filter('pre_comment_user_ip', fn () => '');
        }

        if (isset($theme['remove_emojis']) && $theme['remove_emojis']) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            add_filter('tiny_mce_plugins', fn (array $plugins) => array_diff($plugins, ['wpemoji']));
            add_filter(
                'wp_resource_hints',
                function (array $urls, string $relationType) {
                    if ($relationType === 'dns-prefetch') {
                        // This filter is documented in wp-includes/formatting.php
                        $emojiSvgUrl = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');

                        $urls = array_diff($urls, [$emojiSvgUrl]);
                    }

                    return $urls;
                },
                10,
                2,
            );
        }

        add_action('wp_enqueue_scripts', function () use ($theme) {
            // Remove block library CSS
            if (isset($theme['disable_gutenberg']) && $theme['disable_gutenberg']) {
                wp_dequeue_style('wp-block-library');
            }
            // Remove WP Embed script
            if (isset($theme['disable_wp_embed']) && $theme['disable_wp_embed']) {
                wp_deregister_script('wp-embed');
            }
            // Disable comment reply script if not needed
            if (
                isset($theme['automatic_comments_script']) &&
                $theme['automatic_comments_script'] &&
                get_queried_object() instanceof \WP_Post &&
                comments_open()
            ) {
                wp_enqueue_script('comment-reply');
            }
        });

        add_action('init', function () use ($theme) {
            // Disable Gutenberg
            if (isset($theme['disable_gutenberg']) && $theme['disable_gutenberg']) {
                add_filter('use_block_editor_for_post', '__return_false');
            }
        });

        add_action('after_setup_theme', function () use ($theme) {
            // Load theme text domain
            if (isset($theme['text_domain']) && $theme['text_domain']) {
                if (!isset($theme['text_domain']['domain'])) {
                    throw new InvalidArgumentException('You need to set a domain to add a theme text domain.');
                }
                if (!isset($theme['text_domain']['path'])) {
                    throw new InvalidArgumentException('You need to set a path to add a theme text domain.');
                }
                load_theme_textdomain($theme['text_domain']['domain'], get_stylesheet_directory_uri() . $theme['text_domain']['path']);
            }
        });
    }
}
