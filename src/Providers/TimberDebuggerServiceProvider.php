<?php

namespace themes\Wordpress\Framework\Core\Providers;

use Ajgl\Twig\Extension\BreakpointExtension;
use themes\Wordpress\Framework\Core\Config;
use Djboris88\Twig\Extension\CommentedIncludeExtension;
use HelloNico\Twig\DumpExtension;

class TimberDebuggerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(Config $config)
    {
        if (defined('WP_DEBUG') && WP_DEBUG && function_exists('add_filter')) {
            add_filter('timber/loader/twig', function ($twig) {
                $twig->addExtension(new CommentedIncludeExtension());
                $twig->addExtension(new DumpExtension());
                $twig->addExtension(new BreakpointExtension());

                return $twig;
            });

            /*
             * Adding a second filter to cover the `Timber::render()` case, when the
             * template is not loaded through the `include` tag inside a twig file
             */
            add_filter(
                'timber/output',
                function ($output, $data, $file) {
                    return "\n<!-- Begin output of '" . $file . "' -->\n" . $output . "\n<!-- / End output of '" . $file . "' -->\n";
                },
                10,
                3,
            );
        }
    }
}
