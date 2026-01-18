<?php

namespace themes\Wordpress\Framework\Core\Providers;

use themes\Wordpress\Framework\Core\Admin\Notices\Notice;
use themes\Wordpress\Framework\Core\Admin\Notices\Types\Warning;
use themes\Wordpress\Framework\Core\Config;

class TinyMceServiceProvider extends ServiceProvider
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
        $tinyMce = $config->get('tinymce');

        if (!$tinyMce) {
            return;
        }

        // Add the button Formats to TinyMCE
        add_filter('mce_buttons_2', function ($buttons) {
            array_unshift($buttons, 'styleselect');

            return $buttons;
        });

        // Attach style formats
        add_filter('tiny_mce_before_init', function ($initArray) use ($tinyMce) {
            $initArray['style_formats'] = json_encode($tinyMce);

            return $initArray;
        });

        if (!$config->get('assets')['editor_style']) {
            (new Notice(
                __('You need to add an editor style file to `config/assets.php` to have the formatting of elements with TinyMCE.'),
                Warning::class,
            ))->render();
        }
    }
}
