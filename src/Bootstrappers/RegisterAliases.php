<?php

namespace themes\Wordpress\Framework\Core\Bootstrappers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\PackageManifest;

class RegisterAliases
{
    public function bootstrap(Application $app)
    {
        $config = $app->get('config');

        /** @var PackageManifest $packageManifest */
        $packageManifest = $app->make(PackageManifest::class);

        foreach (array_merge($config->get('app.aliases', []), $packageManifest->aliases()) as $alias => $realClassname) {
            class_alias($realClassname, $alias);
        }
    }
}
