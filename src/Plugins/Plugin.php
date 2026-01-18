<?php

namespace themes\Wordpress\Framework\Core\Plugins;

abstract class Plugin
{
    /**
     * Clean plugins assets (js, css, ...)
     *
     * @return void
     */
    abstract public function clean(): void;
}
