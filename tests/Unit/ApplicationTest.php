<?php

namespace themes\Wordpress\Framework\Core\Test\Unit;

use themes\Wordpress\Framework\Core\Application;
use Exception;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * @preserveGlobalState disabled
 */
class ApplicationTest extends TestCase
{
    protected vfsStreamDirectory $rootFileSystem;

    protected string $vfsStreamDirectoryName = 'exampleDir';

    public function testItInstantiatesWithBasePath()
    {
        $app = new Application(vfsStream::url($this->vfsStreamDirectoryName));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName), $app->get('path.base'));
        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName), $app->basePath());
    }

    public function testItInstantiatesWithBedrockPath()
    {
        vfsStream::create(
            [
                'htdocs' => [
                    'app' => [
                        'themes' => [
                            'theme' => [],
                        ],
                    ],
                ],
            ],
            $this->rootFileSystem,
        );

        $app = new Application(vfsStream::url($this->vfsStreamDirectoryName . '/htdocs/app/themes/theme/'));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName), $app->bedrockPath());
    }

    public function testItInstantiatesWithCustomPaths()
    {
        vfsStream::create(
            [
                'config' => [],
                'resources' => [],
            ],
            $this->rootFileSystem,
        );

        $configDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/config');
        $resourcesDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/resources');

        $app = new Application(false, [
            'config' => vfsStream::url($this->vfsStreamDirectoryName . '/config'),
            'resources' => vfsStream::url($this->vfsStreamDirectoryName . '/resources'),
        ]);

        $this->assertSame($configDirectory, $app->get('path.config'));
        $this->assertSame($configDirectory, $app->configPath());
        $this->assertSame($resourcesDirectory, $app->get('path.resources'));
        $this->assertSame($resourcesDirectory, $app->resourcePath());
    }

    public function testItRejectsInvalidCustomPathTypes()
    {
        vfsStream::create(
            [
                'config' => [],
            ],
            $this->rootFileSystem,
        );

        $configDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/config');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The not_a_valid_path_type path type is not supported.');

        new Application(false, [
            'config' => $configDirectory,
            'not_a_valid_path_type' => vfsStream::url($this->vfsStreamDirectoryName),
        ]);
    }

    public function testItRejectsInvalidCustomPaths()
    {
        vfsStream::create(
            [
                'config' => [],
            ],
            $this->rootFileSystem,
        );

        $configDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/config');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf('The %s directory must be present.', sprintf('%s/this/does/not/exist', __DIR__)));

        new Application(false, [
            'config' => $configDirectory,
            'resources' => sprintf('%s/this/does/not/exist', __DIR__),
        ]);
    }

    public function testItAcceptsAnArrayOfCustomPaths()
    {
        // Prepare the structure to use after the first call to the application with another structure
        vfsStream::create(
            [
                // First Application will use this base
                'base' => [
                    'app' => [],
                    'bootstrap' => [],
                    'config' => [],
                    'resources' => [],
                ],
                // Second application will use overwrite
                'overwrite' => [
                    'app' => [],
                    'bootstrap' => [],
                    'config' => [],
                    'resources' => [],
                ],
            ],
            $this->rootFileSystem,
        );

        $appDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/overwrite/app');
        $bootstrapDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/overwrite/bootstrap');
        $configDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/overwrite/config');
        $resourcesDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/overwrite/resources');

        $app = new Application(vfsStream::url($this->vfsStreamDirectoryName . '/base'));

        $this->assertNotSame($appDirectory, $app->get('path.app'));
        $this->assertNotSame($appDirectory, $app->appPath());
        $this->assertNotSame($bootstrapDirectory, $app->get('path.bootstrap'));
        $this->assertNotSame($bootstrapDirectory, $app->bootstrapPath());
        $this->assertNotSame($configDirectory, $app->get('path.config'));
        $this->assertNotSame($configDirectory, $app->configPath());
        $this->assertNotSame($resourcesDirectory, $app->get('path.resources'));
        $this->assertNotSame($resourcesDirectory, $app->resourcePath());

        $app->usePaths([
            'app' => $appDirectory,
            'bootstrap' => $bootstrapDirectory,
            'config' => $configDirectory,
            'resources' => $resourcesDirectory,
        ]);

        $this->assertSame($appDirectory, $app->get('path.app'));
        $this->assertSame($appDirectory, $app->appPath());
        $this->assertSame($bootstrapDirectory, $app->get('path.bootstrap'));
        $this->assertSame($bootstrapDirectory, $app->bootstrapPath());
        $this->assertSame($configDirectory, $app->get('path.config'));
        $this->assertSame($configDirectory, $app->configPath());
        $this->assertSame($resourcesDirectory, $app->get('path.resources'));
        $this->assertSame($resourcesDirectory, $app->resourcePath());
    }

    public function testItAllowsSpecificPathsToBeChanged()
    {
        vfsStream::create(
            [
                'app' => [],
                'bootstrap' => [],
                'config' => [],
                'resources' => [],
            ],
            $this->rootFileSystem,
        );

        $appDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/app');
        $bootstrapDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/bootstrap');
        $configDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/config');
        $resourcesDirectory = vfsStream::url($this->vfsStreamDirectoryName . '/resources');

        $app = new Application('not_a_path');

        $this->assertNotSame($appDirectory, $app->get('path.app'));
        $this->assertNotSame($appDirectory, $app->appPath());
        $app->useAppPath($appDirectory);
        $this->assertSame($appDirectory, $app->get('path.app'));
        $this->assertSame($appDirectory, $app->appPath());

        $this->assertNotSame($bootstrapDirectory, $app->get('path.bootstrap'));
        $this->assertNotSame($bootstrapDirectory, $app->bootstrapPath());
        $app->useBootstrapPath($bootstrapDirectory);
        $this->assertSame($bootstrapDirectory, $app->get('path.bootstrap'));
        $this->assertSame($bootstrapDirectory, $app->bootstrapPath());

        $this->assertNotSame($configDirectory, $app->get('path.config'));
        $this->assertNotSame($configDirectory, $app->configPath());
        $app->useConfigPath($configDirectory);
        $this->assertSame($configDirectory, $app->get('path.config'));
        $this->assertSame($configDirectory, $app->configPath());

        $this->assertNotSame($resourcesDirectory, $app->get('path.resources'));
        $this->assertNotSame($resourcesDirectory, $app->resourcePath());
        $app->useResourcePath($resourcesDirectory);
        $this->assertSame($resourcesDirectory, $app->get('path.resources'));
        $this->assertSame($resourcesDirectory, $app->resourcePath());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootFileSystem = vfsStream::setup($this->vfsStreamDirectoryName);
    }
}
