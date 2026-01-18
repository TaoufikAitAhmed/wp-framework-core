<?php

namespace themes\Wordpress\Framework\Core\Assets\Contracts;

use themes\Wordpress\Framework\Core\Assets\Contracts\Manifest as ManifestContract;
use InvalidArgumentException;

interface Manager
{
    /**
     * Retrieve path to the assets.
     *
     * @return string
     */
    public function path(): string;

    /**
     * Retrieve the version of the assets.
     *
     * @return string|null
     */
    public function version(): ?string;

    /**
     * Get the manifest.
     *
     * @return ManifestContract
     * @throws InvalidArgumentException
     */
    public function getManifest(): ManifestContract;

    /**
     * Assets have a manifest ?
     *
     * @return bool
     */
    public function hasManifest(): bool;

    /**
     * Get an asset path.
     *
     * @param string $path
     *
     * @return string
     */
    public function get(string $path): string;
}
