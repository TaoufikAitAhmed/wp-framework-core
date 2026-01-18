<?php

namespace themes\Wordpress\Framework\Core;

use Illuminate\Support\Arr;

class Config
{
    protected array $data = [];

    public function __construct(string $path = null)
    {
        if ($path) {
            $this->load($path);
        }
    }

    public function set(string $key, $value): self
    {
        Arr::set($this->data, $key, $value);

        return $this;
    }

    public function get(string $key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    public function has(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    public function load(string $path): self
    {
        // If the config file have a 'wp_action' key, we need to load configuration within the action
        // specified.
        $wpActionRegex = '/\s*(\'|")wp_action(\'|")\s*=>\s*(\'|")(?<wp_action>.*)(\'|")\s*/m';
        $files = glob($path . '/*.php');

        foreach ($files as $file) {
            preg_match($wpActionRegex, file_get_contents($file), $matches);

            if (!isset($matches['wp_action'])) {
                $this->data[pathinfo($file)['filename']] = include $file;
                continue;
            }

            add_action($matches['wp_action'], fn () => $this->data[pathinfo($file)['filename']] = include $file);
        }

        return $this;
    }
}
