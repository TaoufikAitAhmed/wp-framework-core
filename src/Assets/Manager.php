<?php

namespace themes\Wordpress\Framework\Core\Assets;

use themes\Wordpress\Framework\Core\Assets\Contracts\Manager as ManagerContract;
use themes\Wordpress\Framework\Core\Assets\Contracts\Manifest as ManifestContract;
use themes\Wordpress\Framework\Core\Assets\Exceptions\MissingPathException;
use InvalidArgumentException;

class Manager implements ManagerContract
{
    /**
     * The manifest object.
     */
    protected ManifestContract $manifest;

    /**
     * Configuration of assets.
     *
     * @var array{path: string, version?: string|null}
     */
    protected array $config;

    /**
     * Initialize an asset manager.
     *
     * @param array{path: string, version?: string|null} $config
     * @param ManifestContract|null                      $manifest
     *
     * @throws MissingPathException
     */
    public function __construct(array $config, ?ManifestContract $manifest = null)
    {
        if (!isset($config['path'])) {
            throw new MissingPathException('The path for the assets is missing. If you want to use the root of the theme, use `/`.');
        }
        if ($manifest) {
            $this->manifest = $manifest;
        }
        $this->config = $config;
    }

    /** @inheritdoc */
    public function path(): string
    {
        return $this->config['path'];
    }

    /** @inheritdoc */
    public function version(): ?string
    {
        return $this->config['version'] ?? null;
    }

    /** @inheritdoc */
    public function getManifest(): ManifestContract
    {
        if (!$this->hasManifest()) {
            throw new InvalidArgumentException('There is no manifest available.');
        }

        return $this->manifest;
    }

    /** @inheritdoc */
    public function hasManifest(): bool
    {
        return isset($this->manifest);
    }

    /** @inheritdoc */
    public function get(string $path): string
    {
        return sprintf('%s%s/%s', get_stylesheet_directory_uri(), $this->path(), $this->hasManifest() ? $this->getManifest()->asset($path) : $path);
    }
}
