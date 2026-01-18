<?php

namespace themes\Wordpress\Framework\Core\Assets\Contracts;

interface Manifest
{
    /**
     * Get an asset from the Manifest.
     *
     * @param string $key
     *
     * @return string
     */
    public function asset(string $key): string;
}
