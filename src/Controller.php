<?php

namespace themes\Wordpress\Framework\Core;

use themes\Wordpress\Framework\Core\Assets\Concerns\Enqueuable;
use themes\Wordpress\Framework\Core\Plugins\Concerns\HasPlugins;
use themes\Wordpress\Framework\Core\Plugins\PluginCleaner;
use DI\NotFoundException;
use Rareloop\Lumberjack\Helpers;
use Rareloop\Lumberjack\Http\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Should enqueue the assets automatically ?
     *
     * @var bool
     */
    protected bool $enqueue = true;

    /**
     * Instance of PluginCleaner.
     *
     * @var PluginCleaner|null
     */
    protected ?PluginCleaner $pluginCleaner = null;

    public function __construct()
    {
        if (in_array(Enqueuable::class, class_uses_recursive($this), true) && method_exists($this, 'enqueue') && $this->enqueue) {
            $this->enqueue();
        }

        // Clean all plugins
        if ($this->getPluginCleaner()) {
            $this->getPluginCleaner()->cleanAll(
                in_array(HasPlugins::class, class_uses_recursive($this), true) && method_exists($this, 'plugins') ? $this->plugins() : null,
            );
        }
    }

    /**
     * Get PluginCleaner instance if there is plugins specified in config.
     *
     * @return PluginCleaner|null
     */
    protected function getPluginCleaner(): ?PluginCleaner
    {
        try {
            $plugins = Helpers::app('config')->get('plugins');
        } catch (NotFoundException $e) {
            return null;
        }

        if (!$plugins) {
            return null;
        }

        if (!$this->pluginCleaner) {
            $this->pluginCleaner = new PluginCleaner($plugins);
        }

        return $this->pluginCleaner;
    }
}
