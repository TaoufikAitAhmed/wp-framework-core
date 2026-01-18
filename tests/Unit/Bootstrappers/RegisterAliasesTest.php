<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Bootstrappers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Bootstrappers\RegisterAliases;
use themes\Wordpress\Framework\Core\Config;
use themes\Wordpress\Framework\Core\PackageManifest;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

class RegisterAliasesTest extends TestCase
{
    public function testCallsClassAliasOnAllAliasMappings()
    {
        $app = new Application(__DIR__ . '/fixtures/bedrock/htdocs/app/themes/theme');

        $app->singleton(PackageManifest::class, function () use ($app) {
            return new PackageManifest(new Filesystem(), $app->bedrockPath(), $app->getCachedPackagesPath());
        });

        $config = new Config();
        $config->set('app.aliases', [
            'Foo' => TestClassToAlias::class,
        ]);

        $app->bind('config', $config);

        $bootstrapper = new RegisterAliases();
        $bootstrapper->bootstrap($app);

        $this->assertTrue(class_exists('Foo'));
        $this->assertInstanceOf(TestClassToAlias::class, new \Foo());
    }

    public function testRegistersAllProvidersFoundInPackages()
    {
        @unlink(__DIR__ . '/fixtures/bedrock-autoload-packages-aliases/htdocs/app/themes/theme/boostrap/cache/packages.php');
        $app = new Application(__DIR__ . '/fixtures/bedrock-autoload-packages-aliases/htdocs/app/themes/theme');

        $app->singleton(PackageManifest::class, function () use ($app) {
            return new PackageManifest(new Filesystem(), $app->bedrockPath(), $app->getCachedPackagesPath());
        });

        $config = new Config();
        $app->bind('config', $config);

        $bootstrapper = new RegisterAliases();
        $bootstrapper->bootstrap($app);

        $this->assertTrue(class_exists('Bar'));
        $this->assertInstanceOf(TestClassToAlias::class, new \Bar());
    }
}

class TestClassToAlias
{
}
