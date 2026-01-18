<?php

namespace themes\Wordpress\Framework\Core\Test;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Artisan\Artisan;
use Mockery;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class CommandMakerTestCase extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected $rootFileSystem;

    protected $vfsStreamDirectoryName = 'exampleDir';

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootFileSystem = vfsStream::setup($this->vfsStreamDirectoryName);
    }

    protected function callArtisanCommand(Artisan $artisan, $name, array $params = [])
    {
        $artisan->console()->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => $name,
            ] + $params,
        );

        $output = new BufferedOutput();
        $artisan->console()->run($input, $output);

        return $output;
    }

    protected function appWithMockBasePath()
    {
        $app = Mockery::mock(Application::class . '[basePath]');
        $app->shouldReceive('basePath')->andReturn(vfsStream::url($this->vfsStreamDirectoryName));

        return $app;
    }

    protected function assertMockPath($path)
    {
        return $this->assertTrue($this->rootFileSystem->hasChild($path), "Path does not exist: `{$path}`");
    }

    protected function assertMockPathMissing($path)
    {
        return $this->assertFalse($this->rootFileSystem->hasChild($path), "Path exists: `{$path}`");
    }

    protected function requireMockFile($path)
    {
        return require vfsStream::url($this->vfsStreamDirectoryName . '/' . $path);
    }

    protected function getMockFileContents($path)
    {
        return file_get_contents($this->getMockPath($path));
    }

    protected function getMockPath($path)
    {
        return vfsStream::url($this->vfsStreamDirectoryName . '/' . $path);
    }
}
