<?php

namespace themes\Wordpress\Framework\Core\Plugins;

use InvalidArgumentException;
use Rareloop\Lumberjack\Helpers;

class PluginCleaner
{
    /**
     * Plugins to clean.
     *
     * @var array|null
     */
    private array $plugins;

    /**
     * @param array|null $plugins Plugins to clean.
     */
    public function __construct(?array $plugins = [])
    {
        $this->plugins = $plugins;
    }

    /**
     * Clean all plugins.
     *
     * @param array|array<class-string<Plugin>|null $dontCleanPlugins Array of plugins that should not be cleaned.
     *
     * @return void
     */
    public function cleanAll(?array $dontCleanPlugins = []): void
    {
        if (empty($this->all())) {
            return;
        }

        if (!empty($dontCleanPlugins)) {
            foreach ($dontCleanPlugins as $dontCleanPlugin) {
                $this->checkPlugin($dontCleanPlugin);
            }
        }

        foreach (array_diff($this->all(), $dontCleanPlugins ?? []) as $plugin) {
            $this->checkPlugin($plugin);
            $this->clean(Helpers::app($plugin));
        }
    }

    /**
     * Clean a plugin.
     *
     * @param Plugin $plugin
     */
    public function clean(Plugin $plugin): void
    {
        $plugin->clean();
    }

    /**
     * Get all plugins.
     *
     * @return array|null
     */
    public function all(): ?array
    {
        return $this->plugins;
    }

    /**
     * Check expectedPlugin is a class and is an instance of Plugin
     *
     * @param $expectedPlugin
     *
     * @return void
     */
    protected function checkPlugin($expectedPlugin): void
    {
        if (!class_exists($expectedPlugin)) {
            throw new InvalidArgumentException("The {$expectedPlugin} class does not exists.");
        }

        if (!is_a($expectedPlugin, Plugin::class, true)) {
            throw new InvalidArgumentException("The {$expectedPlugin} class is not an instance of Plugin.");
        }
    }
}
