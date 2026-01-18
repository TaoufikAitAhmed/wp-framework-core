<?php

namespace themes\Wordpress\Framework\Core\Bootstrappers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\PackageManifest;
use Illuminate\Filesystem\Filesystem;
use Rareloop\Lumberjack\Providers\LogServiceProvider;

class RegisterProviders
{
    public function bootstrap(Application $app)
    {
        $this->registerPackageManifest($app);
        $this->registerBaseProviders($app);

        /** @var PackageManifest $packageManifest */
        $packageManifest = $app->make(PackageManifest::class);

        $config = $app->get('config');

        $providers = array_merge($config->get('app.providers', []), $packageManifest->providers());

        foreach ($providers as $provider) {
            $app->register($provider);
        }
    }

    /**
     * Register the package manifest.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function registerPackageManifest(Application $app)
    {
        $app->singleton(PackageManifest::class, function () use ($app) {
            return new PackageManifest(new Filesystem(), $app->bedrockPath(), $app->getCachedPackagesPath());
        });
    }

    protected function registerBaseProviders(Application $app)
    {
        $app->register(LogServiceProvider::class);
    }
}
