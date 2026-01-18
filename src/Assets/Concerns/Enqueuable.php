<?php

namespace themes\Wordpress\Framework\Core\Assets\Concerns;

use themes\Wordpress\Framework\Core\Assets\Contracts\Manager as ManagerContract;
use themes\Wordpress\Framework\Core\Assets\Exceptions\MissingPathException;
use Illuminate\Support\Str;
use Rareloop\Lumberjack\Helpers;

trait Enqueuable
{
    /**
     * Get JS files to enqueue.
     *
     * @return array<string, string|array{path: string, enqueue?: bool, data?: array<string, string>, in_footer?: bool}|null>
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    abstract public function js(): array;

    /**
     * Get CSS files to enqueue.
     *
     * @return array<string, string|array{path: string, enqueue?: bool, data?: array<string, string>}|null>
     */
    abstract public function css(): array;

    /**
     * Enqueue CSS files in WordPress.
     *
     * @return $this
     * @throws MissingPathException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function enqueueCss(): self
    {
        $css = $this->css();
        if (!$css) {
            return $this;
        }

        add_action('wp_enqueue_scripts', function () use ($css) {
            collect($css)->map(function ($path, $name) {
                $name = $this->assetName($name);
                $configuration = $this->generateConfiguration($path);

                if (isset($configuration['enqueue']) && !$configuration['enqueue']) {
                    return;
                }

                if ($this->isRemotePath($configuration['path'])) {
                    wp_register_style($name, $configuration['path']);
                } else {
                    wp_register_style($name, $this->getAssets()->get($configuration['path']), [], $this->getAssets()->version() ?? null);
                }

                wp_enqueue_style($name);
            });
        });

        return $this;
    }

    /**
     * Enqueue JS files in WordPress.
     *
     * @return $this
     * @throws MissingPathException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function enqueueJs(): self
    {
        $javascript = $this->js();
        if (!$javascript) {
            return $this;
        }

        add_action('wp_enqueue_scripts', function () use ($javascript) {
            collect($javascript)->map(function ($path, $name) {
                $name = $this->assetName($name);
                $configuration = $this->generateConfiguration($path);

                if (isset($configuration['enqueue']) && !$configuration['enqueue']) {
                    return;
                }

                if ($this->isRemotePath($configuration['path'])) {
                    wp_register_script($name, $configuration['path']);
                } else {
                    wp_register_script(
                        $name,
                        $this->getAssets()->get($configuration['path']),
                        [],
                        $this->getAssets()->version() ?? null,
                        !(array_key_exists('in_footer', $configuration) && $configuration['in_footer'] === false),
                    );
                }

                wp_enqueue_script($name);

                if (isset($configuration['data'])) {
                    wp_localize_script($name, "{$name}Datas", $configuration['data']);
                }
            });
        });

        return $this;
    }

    /**
     * Enqueue JS and CSS files in WordPress.
     *
     * @return $this
     * @throws MissingPathException
     */
    public function enqueue(): self
    {
        return $this->enqueueCss()->enqueueJs();
    }

    /**
     * Is a remote path ?
     *
     * @param string $path
     *
     * @return bool
     */
    private function isRemotePath(string $path): bool
    {
        $urlRegex =
            '/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/m';

        return (bool) preg_match($urlRegex, $path);
    }

    /**
     * Generate a configuration from a string or an array.
     *
     * @param string|array{path: string, enqueue?: bool, data?: array<string, string>, in_footer?: bool} $from
     *
     * @return array{path: string, enqueue?: bool, data?: array<string, string>, in_footer?: bool}
     * @throws MissingPathException
     */
    private function generateConfiguration($from): array
    {
        if (!is_array($from)) {
            return [
                'path' => $from,
            ];
        }
        if (!isset($from['path'])) {
            throw new MissingPathException('Path is missing for an asset.');
        }

        return $from;
    }

    /**
     * Return the asset name used for the WordPress handle parameter.
     *
     * @param string|int $name
     *
     * @return string
     */
    private function assetName($name): string
    {
        return is_int($name) ? Str::uuid() : $name;
    }

    /**
     * Get assets from the container.
     *
     * @return ManagerContract
     */
    private function getAssets(): ManagerContract
    {
        return Helpers::app('assets');
    }
}
