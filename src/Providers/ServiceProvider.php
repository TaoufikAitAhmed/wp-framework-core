<?php

namespace themes\Wordpress\Framework\Core\Providers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Artisan\Artisan;
use themes\Wordpress\Framework\Core\Config;
use Twig\Loader\FilesystemLoader;

abstract class ServiceProvider
{
    /**
     * The paths that should be published.
     *
     * @var array
     */
    public static array $publishes = [];

    /**
     * The paths that should be published by group.
     *
     * @var array
     */
    public static array $publishGroups = [];

    /**
     * Application instance.
     *
     * @var Application
     */
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Merge the config in the provided path into what already exists. Existing config takes
     * priority over what is found in $path.
     *
     * @param string $path
     * @param string $key
     *
     * @return void
     */
    public function mergeConfigFrom(string $path, string $key)
    {
        $existing = $this->app->get(Config::class)->get($key, []);
        $this->app->get(Config::class)->set($key, array_merge(require $path, $existing));
    }

    /**
     * Get the paths to publish.
     *
     * @param string|null $provider
     * @param string|null $group
     *
     * @return array
     */
    public static function pathsToPublish($provider = null, $group = null): array
    {
        if (!is_null($paths = static::pathsForProviderOrGroup($provider, $group))) {
            return $paths;
        }

        return collect(static::$publishes)->reduce(function ($paths, $p) {
            return array_merge($paths, $p);
        }, []);
    }

    /**
     * Get the service providers available for publishing.
     *
     * @return array
     */
    public static function publishableProviders(): array
    {
        return array_keys(static::$publishes);
    }

    /**
     * Get the groups available for publishing.
     *
     * @return array
     */
    public static function publishableGroups(): array
    {
        return array_keys(static::$publishGroups);
    }

    /**
     * Register the package's custom Artisan commands.
     *
     * @param array|mixed $commands
     *
     * @return void
     */
    public function commands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();
        /** @var Artisan $artisan */
        $artisan = $this->app->get(Artisan::class);

        foreach ($commands as $command) {
            $artisan->console()->add($this->app->make($command));
        }
    }

    /**
     * Register paths to be published by the publish command.
     *
     * @param array $paths
     * @param mixed $groups
     *
     * @return void
     */
    protected function publishes(array $paths, $groups = null)
    {
        $this->ensurePublishArrayInitialized($class = static::class);

        static::$publishes[$class] = array_merge(static::$publishes[$class], $paths);

        foreach ((array) $groups as $group) {
            $this->addPublishGroup($group, $paths);
        }
    }

    /**
     * Ensure the publish array for the service provider is initialized.
     *
     * @param string $class
     *
     * @return void
     */
    protected function ensurePublishArrayInitialized(string $class)
    {
        if (!array_key_exists($class, static::$publishes)) {
            static::$publishes[$class] = [];
        }
    }

    /**
     * Add a publish group / tag to the service provider.
     *
     * @param string $group
     * @param array  $paths
     *
     * @return void
     */
    protected function addPublishGroup(string $group, array $paths)
    {
        if (!array_key_exists($group, static::$publishGroups)) {
            static::$publishGroups[$group] = [];
        }

        static::$publishGroups[$group] = array_merge(static::$publishGroups[$group], $paths);
    }

    /**
     * Get the paths for the provider or group (or both).
     *
     * @param string|null $provider
     * @param string|null $group
     *
     * @return array
     */
    protected static function pathsForProviderOrGroup(?string $provider, ?string $group): array
    {
        if ($provider && $group) {
            return static::pathsForProviderAndGroup($provider, $group);
        } elseif ($group && array_key_exists($group, static::$publishGroups)) {
            return static::$publishGroups[$group];
        } elseif ($provider && array_key_exists($provider, static::$publishes)) {
            return static::$publishes[$provider];
        } elseif ($group || $provider) {
            return [];
        }

        return [];
    }

    /**
     * Register a view file namespace.
     *
     * @param string $path
     * @param string $namespace
     *
     * @return void
     * @throws \Twig\Error\LoaderError
     */
    protected function loadViewsFrom(string $path, string $namespace): void
    {
        add_filter('timber/loader/loader', function (FilesystemLoader $loader) use ($namespace, $path) {
            if (is_dir($this->app->resourcePath("views/vendor/{$namespace}"))) {
                $loader->addPath($this->app->resourcePath("views/vendor/{$namespace}"), $namespace);
            } else {
                $loader->addPath($path, $namespace);
            }

            return $loader;
        });
    }

    /**
     * Get the paths for the provider and group.
     *
     * @param string $provider
     * @param string $group
     *
     * @return array
     */
    protected static function pathsForProviderAndGroup(string $provider, string $group): array
    {
        if (!empty(static::$publishes[$provider]) && !empty(static::$publishGroups[$group])) {
            return array_intersect_key(static::$publishes[$provider], static::$publishGroups[$group]);
        }

        return [];
    }
}
