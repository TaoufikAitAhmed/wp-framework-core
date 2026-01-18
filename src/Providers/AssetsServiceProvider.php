<?php

namespace themes\Wordpress\Framework\Core\Providers;

use themes\Wordpress\Framework\Core\Assets\Concerns\Enqueuable;
use themes\Wordpress\Framework\Core\Assets\Contracts\Manager as ManagerContract;
use themes\Wordpress\Framework\Core\Assets\Contracts\Manifest as ManifestContract;
use themes\Wordpress\Framework\Core\Assets\Exceptions\MissingPathException;
use themes\Wordpress\Framework\Core\Assets\Manager;
use themes\Wordpress\Framework\Core\Assets\Manifest;
use themes\Wordpress\Framework\Core\Config;
use Timber\Twig_Function;
use Twig\Environment;

/**
 * @SuppressWarnings(PHPMD.ShortMethodName)
 */
class AssetsServiceProvider extends ServiceProvider
{
    use Enqueuable;

    /**
     * @var array{
     *     scripts?: array<string, string|array{path: string, enqueue?: bool, data?: array<string, string>, in_footer?: bool}|null>,
     *     styles?: array<string, string|array{path: string, enqueue?: bool, data?: array<string, string>}|null>
     *  }
     */
    private array $config;

    /**
     * Bootstrap services.
     *
     * @param Config $config
     *
     * @return void
     * @throws MissingPathException
     */
    public function boot(Config $config): void
    {
        $this->config = $config->get('assets');
        $this->enqueue();

        if (isset($this->config['editor_style'])) {
            add_editor_style($this->app->get('assets')->get($this->config['editor_style']));
        }

        add_filter('timber/twig', function (Environment $twig) {
            $twig->addFunction(
                new Twig_Function('asset', function (string $path = '') {
                    return $this->app->make('assets')->get($path);
                }),
            );

            return $twig;
        });
    }

    /**
     * Register services.
     *
     * @return void
     * @throws MissingPathException
     */
    public function register(): void
    {
        $config = $this->app->get('config')->get('assets');

        $manifest = isset($config['manifest']) && $config['manifest']['enable'] ? new Manifest($config['manifest']['path']) : null;

        if ($manifest) {
            $this->app->singleton('assets.manifest', fn () => $manifest);
            $this->app->bind(ManifestContract::class, $manifest);
            $this->app->bind(Manifest::class, $manifest);
        }

        $manager = new Manager(
            [
                'path'    => $config['directory'],
                'version' => isset($config['version']) ? $config['version'] : null,
            ],
            $this->app->has('assets.manifest') ? $this->app->get('assets.manifest') : null,
        );

        $this->app->singleton('assets', fn () => $manager);
        $this->app->bind(ManagerContract::class, $manager);
        $this->app->bind(Manager::class, $manager);
    }

    /** @inheritDoc */
    public function js(): array
    {
        return $this->config['scripts'] ?? [];
    }

    /** @inheritDoc */
    public function css(): array
    {
        return $this->config['styles'] ?? [];
    }
}
