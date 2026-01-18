<?php

namespace themes\Wordpress\Framework\Core\Assets;

use themes\Wordpress\Framework\Core\Assets\Contracts\Manifest as ManifestContract;
use RuntimeException;

class Manifest implements ManifestContract
{
    /**
     * Path to the manifest file.
     */
    protected string $manifestPath;

    public function __construct(string $path)
    {
        $this->manifestPath = $path;
    }

    /** @inheritDoc */
    public function asset(string $key): string
    {
        $manifestContent = $this->content();

        return !$manifestContent || (is_array($manifestContent) && !array_key_exists($key, $manifestContent)) ? $key : $manifestContent[$key];
    }

    /**
     * Manifest content.
     *
     * Usually a JSON looking like that :
     *
     * {
     *  "css/app.css": "css/app-698514236.css
     * }
     *
     * @return null|array<string, string>
     */
    protected function content(): ?array
    {
        if (!file_exists($this->manifestPath)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw new RuntimeException("The manifest at the path {$this->manifestPath} cannot be found.");
            }

            return null;
        }
        $content = file_get_contents($this->manifestPath);

        return $content ? json_decode($content, true) : null;
    }
}
