<?php

namespace themes\Wordpress\Framework\Core\Plugins\Concerns;

trait HasPlugins
{
    /**
     * Plugins that should be loaded.
     *
     * @return array|class-string<\themes\Wordpress\Framework\Core\Plugins\Plugin>[]
     */
    abstract public function plugins(): array;
}
