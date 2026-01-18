<?php

namespace themes\Wordpress\Framework\Core\Providers;

use themes\Wordpress\Framework\Core\Config;
use Timber\Post;
use Timber\Timber;
use Timber\Twig_Function;
use Twig\Environment;

class TimberServiceProvider extends ServiceProvider
{
    public function register()
    {
        $timber = new Timber();

        $this->app->bind('timber', $timber);
        $this->app->bind(Timber::class, $timber);
    }

    public function boot(Config $config)
    {
        $paths = $config->get('timber.paths');

        if ($paths) {
            Timber::$dirname = $paths;
        }

        add_filter('timber/twig', [$this, 'addTwigHelpers']);
    }

    /**
     * Get an HTML image element representing an image attachment in Wordpress.
     *
     * @param int|Post     $attachment
     * @param string       $size
     * @param bool         $icon
     * @param array|string $attr
     *
     * @return string
     */
    public function getWordpressImage($attachment, string $size = 'full', bool $icon = false, $attr = ''): string
    {
        if ($attachment instanceof Post) {
            $attachment = $attachment->ID;
        } elseif (is_array($attachment) && isset($attachment['ID'])) {
            $attachment = $attachment['ID'];
        }

        return wp_get_attachment_image($attachment, $size, $icon, $attr);
    }

    /**
     * Add Twig helpers.
     *
     * @param Environment $twig
     *
     * @return Environment
     */
    public function addTwigHelpers(Environment $twig): Environment
    {
        $twig->addFunction(new Twig_Function('wp_image', [$this, 'getWordpressImage']));

        return $twig;
    }
}
