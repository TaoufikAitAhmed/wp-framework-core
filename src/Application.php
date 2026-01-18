<?php

namespace themes\Wordpress\Framework\Core;

use Exception;
use Illuminate\Support\Str;
use Invoker\InvokerInterface;
use Rareloop\Lumberjack\Application as FoundationApplication;
use Rareloop\Router\Invoker;

class Application extends FoundationApplication
{
    /**
     * The custom config path defined by the developer.
     *
     * @var string|null
     */
    protected ?string $configPath = null;

    /**
     * The invoker of the application.
     *
     * @var InvokerInterface|null
     */
    protected ?InvokerInterface $invoker = null;

    /**
     * The custom resources path defined by the developer.
     *
     * @var string|null
     */
    protected ?string $resourcesPath = null;

    /**
     * The custom bootstrap path defined by the developer.
     *
     * @var string|null
     */
    protected ?string $bootstrapPath = null;

    /**
     * The custom app path defined by the developer.
     *
     * @var string|null
     */
    protected ?string $appPath = null;

    /**
     * Indicates if the application is running in the console.
     *
     * @var bool|null
     */
    protected ?bool $isRunningInConsole = null;

    /**
     * The prefixes of absolute cache paths for use during normalization.
     *
     * @var string[]
     */
    protected array $absoluteCachePathPrefixes = ['/', '\\'];

    /**
     * Create an application instance.
     *
     * @param string|bool $basePath
     *
     * @throws Exception
     */
    public function __construct($basePath = false, ?array $paths = null)
    {
        parent::__construct($basePath);

        if ($paths) {
            $this->usePaths($paths);
        }
    }

    /**
     * Get the base path of the theme installation.
     *
     * @param string $path
     *
     * @return string
     */
    public function basePath(string $path = ''): string
    {
        return parent::basePath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the bedrock path.
     *
     * @param string $path
     *
     * @return string
     */
    public function bedrockPath(string $path = ''): string
    {
        return dirname(parent::basePath(), 4) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Add new prefix to list of absolute path prefixes.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function addAbsoluteCachePathPrefix(string $prefix): self
    {
        $this->absoluteCachePathPrefixes[] = $prefix;

        return $this;
    }

    /**
     * Set paths that are configurable by the developer.
     *
     * Supported path types:
     *
     * - app
     * - bootstrap
     * - config
     * - resources
     *
     * @param array $paths
     *
     * @return $this
     * @throws Exception
     */
    public function usePaths(array $paths): self
    {
        $supportedPaths = [
            'app'       => 'appPath',
            'bootstrap' => 'bootstrapPath',
            'config'    => 'configPath',
            'resources' => 'resourcesPath',
        ];

        foreach ($paths as $pathType => $path) {
            $path = rtrim($path, '\\/');

            if (!isset($supportedPaths[$pathType])) {
                throw new Exception("The {$pathType} path type is not supported.");
            }

            if (!is_dir($path) || !is_readable($path)) {
                throw new Exception("The {$path} directory must be present.");
            }

            $this->{$supportedPaths[$pathType]} = $path;
        }

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Determine if the application is running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        if ($this->isRunningInConsole === null) {
            $this->isRunningInConsole = (\PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg');
        }

        return $this->isRunningInConsole;
    }

    /**
     * Get the path to the resources directory.
     *
     * @param string $path
     *
     * @return string
     */
    public function resourcePath(string $path = ''): string
    {
        return ($this->resourcesPath ?: $this->basePath() . DIRECTORY_SEPARATOR . 'resources') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Set the resources directory.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useResourcePath(string $path): self
    {
        $this->resourcesPath = $path;

        $this->bind('path.resources', $path);

        return $this;
    }

    /**
     * Get the path to the application configuration files.
     *
     * @param string $path
     *
     * @return string
     */
    public function configPath(string $path = ''): string
    {
        return ($this->configPath ?: $this->basePath() . DIRECTORY_SEPARATOR . 'config') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Set the config directory.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useConfigPath(string $path): self
    {
        $this->configPath = $path;

        $this->bind('path.config', $path);

        return $this;
    }

    /**
     * Get the path to the bootstrap directory.
     *
     * @param string $path Optionally, a path to append to the bootstrap path
     *
     * @return string
     */
    public function bootstrapPath(string $path = ''): string
    {
        return ($this->bootstrapPath ?: $this->basePath() . DIRECTORY_SEPARATOR . 'bootstrap') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Call the given function using the given parameters.
     *
     * Missing parameters will be resolved from the container.
     *
     * @param callable $callable   Function to call.
     * @param array    $parameters Parameters to use. Can be indexed by the parameter names
     *                             or not indexed (same order as the parameters).
     *                             The array can also contain DI definitions, e.g. DI\get().
     *
     * @return mixed Result of the function.
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    public function call(callable $callable, array $parameters = [])
    {
        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * Set the bootstrap directory.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useBootstrapPath(string $path): self
    {
        $this->bootstrapPath = $path;

        $this->bind('path.bootstrap', $path);

        return $this;
    }

    /**
     * Get the path to the app directory.
     *
     * @param string $path Optionally, a path to append to the app path.
     *
     * @return string
     */
    public function appPath(string $path = ''): string
    {
        return ($this->appPath ?: $this->basePath() . DIRECTORY_SEPARATOR . 'app') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Set the app directory.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useAppPath(string $path): self
    {
        $this->appPath = $path;

        $this->bind('path.app', $path);

        return $this;
    }

    /**
     * Get the path to the cached packages.php file.
     *
     * @return string
     */
    public function getCachedPackagesPath(): string
    {
        return $this->normalizeCachePath('APP_PACKAGES_CACHE', 'cache/packages.php');
    }

    /**
     * Get the environment of the application.
     *
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function environment(): string
    {
        return $this->get('config')->get('app.environment');
    }

    protected function getInvoker(): InvokerInterface
    {
        if (!$this->invoker) {
            $this->invoker = new Invoker($this);
        }

        return $this->invoker;
    }

    /**
     * Normalize a relative or absolute path to a cache file.
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    protected function normalizeCachePath(string $key, string $default): string
    {
        $env = getenv($key);
        if (!$env) {
            return $this->bootstrapPath($default);
        }

        return Str::startsWith($env, $this->absoluteCachePathPrefixes) ? $env : $this->basePath($env);
    }

    /**
     * Bind all of the application paths in the container.
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        $this->bind('path.base', $this->basePath());
        $this->bind('path.app', $this->appPath());
        $this->bind('path.config', $this->configPath());
        $this->bind('path.bootstrap', $this->bootstrapPath());
        $this->bind('path.resources', $this->resourcePath());
    }
}
