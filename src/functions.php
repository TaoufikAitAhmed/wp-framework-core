<?php

use Rareloop\Lumberjack\Helpers;

if (!function_exists('asset')) {
    /**
     * Get an asset path.
     *
     * @param string $path
     ** @return string
     */
    function asset(string $path): string
    {
        return Helpers::app('assets')->get($path);
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the base path of the theme installation.
     *
     * @param string $path
     *
     * @return string
     */
    function base_path(string $path = ''): string
    {
        return Helpers::app()->basePath($path);
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the app directory.
     *
     * @param string $path Optionally, a path to append to the app path.
     *
     * @return string
     */
    function app_path(string $path = ''): string
    {
        return Helpers::app()->appPath($path);
    }
}

if (!function_exists('boostrap_path')) {
    /**
     * Get the path to the bootstrap directory.
     *
     * @param string $path Optionally, a path to append to the bootstrap path
     *
     * @return string
     */
    function bootstrap_path(string $path = ''): string
    {
        return Helpers::app()->bootstrapPath($path);
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the path to the application configuration files.
     *
     * @param string $path
     *
     * @return string
     */
    function config_path(string $path = ''): string
    {
        return Helpers::app()->configPath($path);
    }
}

if (!function_exists('resources_path')) {
    /**
     * Get the path to the resources directory.
     *
     * @param string $path
     *
     * @return string
     */
    function resource_path(string $path = ''): string
    {
        return Helpers::app()->resourcePath($path);
    }
}

if (!function_exists('enqueue_alert_styles')) {
    /**
     * Enqueue alert styles.
     */
    function enqueue_alert_styles()
    {
        add_action('wp_head', function () {
            echo <<<HTML
                                <style>
                                    .alert {
                                        position: relative;
                                        padding: 0.75rem 1.25rem;
                                        margin-bottom: 1rem;
                                        border: 1px solid transparent;
                                        border-radius: 0.25rem;
                                    }
                                    .alert-info {
                                        color: #0c5460;
                                        background-color: #d1ecf1;
                                        border-color: #bee5eb;
                                    }
                                    .alert-danger {
                                        color: #721c24;
                                        background-color: #f8d7da;
                                        border-color: #f5c6
                                    }
                                    .alert-warning {
                                        color: #856404;
                                        background-color: #fff3cd;
                                        border-color: #ffeeba;
                                    }
                                    .alert-link {
                                        font-weight: 700;
                                    }
                                    .alert-info .alert-link {
                                        color: #062c33;
                                    }
                                    .alert-danger .alert-link {
                                        color: #491217;
                                    }
                                    .alert-warning .alert-link {
                                        color: #533f03;
                                    }
                                </style>
                HTML;
        }, 100);
    }
}
