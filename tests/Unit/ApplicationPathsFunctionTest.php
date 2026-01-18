<?php

namespace themes\Wordpress\Framework\Core\Test\Unit;

use themes\Wordpress\Framework\Core\Application;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * @preserveGlobalState disabled
 */
class ApplicationPathsFunctionTest extends TestCase
{
    protected vfsStreamDirectory $rootFileSystem;

    protected string $vfsStreamDirectoryName = 'exampleDir';

    public function testBasePathFunctionIsRegistered()
    {
        $this->assertTrue(function_exists('base_path'));
    }

    public function testBasePathFunctionReturnTheBasePath()
    {
        new Application(vfsStream::url($this->vfsStreamDirectoryName));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName), base_path());
    }

    public function testBasePathFunctionCanReturnAnAppendedPath()
    {
        new Application(vfsStream::url($this->vfsStreamDirectoryName));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName . '/file.txt'), base_path('file.txt'));
    }

    public function testAppPathFunctionIsRegistered()
    {
        $this->assertTrue(function_exists('app_path'));
    }

    public function testAppPathFunctionReturnTheAppPath()
    {
        new Application(vfsStream::url($this->vfsStreamDirectoryName));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName . '/app'), app_path());
    }

    public function testAppPathFunctionCanReturnAnAppendedPath()
    {
        new Application(vfsStream::url($this->vfsStreamDirectoryName));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName . '/app/app_inner'), app_path('app_inner'));
    }

    public function testBootstrapPathFunctionIsRegistered()
    {
        $this->assertTrue(function_exists('bootstrap_path'));
    }

    public function testBootstrapPathFunctionReturnTheBootstrapPath()
    {
        new Application(vfsStream::url($this->vfsStreamDirectoryName));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName . '/bootstrap'), bootstrap_path());
    }

    public function testBootstrapPathFunctionCanReturnAnAppendedPath()
    {
        new Application(vfsStream::url($this->vfsStreamDirectoryName));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName . '/bootstrap/bootstrap_inner'), bootstrap_path('bootstrap_inner'));
    }

    public function testConfigPathFunctionIsRegistered()
    {
        $this->assertTrue(function_exists('config_path'));
    }

    public function testConfigPathFunctionReturnTheConfigPath()
    {
        new Application(vfsStream::url($this->vfsStreamDirectoryName));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName . '/config'), config_path());
    }

    public function testConfigPathFunctionCanReturnAnAppendedPath()
    {
        new Application(vfsStream::url($this->vfsStreamDirectoryName));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName . '/config/config_inner'), config_path('config_inner'));
    }

    public function testResourcePathFunctionIsRegistered()
    {
        $this->assertTrue(function_exists('resource_path'));
    }

    public function testResourcePathFunctionReturnTheResourcePath()
    {
        new Application(vfsStream::url($this->vfsStreamDirectoryName));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName . '/resources'), resource_path());
    }

    public function testResourcePathFunctionCanReturnAnAppendedPath()
    {
        new Application(vfsStream::url($this->vfsStreamDirectoryName));

        $this->assertSame(vfsStream::url($this->vfsStreamDirectoryName . '/resources/resources_inner'), resource_path('resources_inner'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        include_once sprintf('%s/../../src/functions.php', __DIR__);
        $this->rootFileSystem = vfsStream::setup($this->vfsStreamDirectoryName);
        vfsStream::create(
            [
                'app' => [
                    'app_inner' => [],
                ],
                'bootstrap' => [
                    'bootstrap_inner' => [],
                ],
                'config' => [
                    'config_inner' => [],
                ],
                'resources' => [
                    'resources_inner' => [],
                ],
                'file.txt',
            ],
            $this->rootFileSystem,
        );
    }
}
