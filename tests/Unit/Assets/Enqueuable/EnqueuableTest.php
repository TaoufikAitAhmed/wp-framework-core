<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable;

use themes\Wordpress\Framework\Core\Assets\Exceptions\MissingPathException;
use themes\Wordpress\Framework\Core\Assets\Manager;
use themes\Wordpress\Framework\Core\Assets\Manifest;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script\DontEnqueueScript;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script\EmptyScript;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script\RemoteScript;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script\ScriptNotInFooter;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script\ScriptWithData;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script\ScriptWithName;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script\ScriptWithNameAndConfiguration;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script\ScriptWithoutName;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script\ScriptWithoutNameButConfiguration;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script\ScriptWithoutPathInConfiguration;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script\ScriptWithVersion;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style\DontEnqueueStyle;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style\EmptyStyle;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style\RemoteStyle;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style\StyleWithName;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style\StyleWithNameAndConfiguration;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style\StyleWithoutName;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style\StyleWithoutNameButConfiguration;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style\StyleWithoutPathInConfiguration;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style\StyleWithVersion;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Illuminate\Support\Str;
use Mockery;
use Rareloop\Lumberjack\Application;

/**
 * @preserveGlobalState disabled
 * @runTestsInSeparateProcesses
 */
class EnqueuableTest extends WordpressTestCase
{
    public function testItEnqueueAStyleWithoutAName()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $mockery = Mockery::mock(sprintf('alias:%s', Str::class));
        $mockery
            ->shouldReceive('uuid')
            ->once()
            ->andReturn('2e7caa8a-7013-44fe-a40b-83a59e53525d');

        $app->bind('assets', new Manager(['path' => '/dist']));

        Functions\expect('wp_register_style')
            ->once()
            ->with('2e7caa8a-7013-44fe-a40b-83a59e53525d', 'https://example.com/app/themes/theme/dist/css/app.css', [], null);
        Functions\expect('wp_enqueue_style')
            ->once()
            ->with('2e7caa8a-7013-44fe-a40b-83a59e53525d');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new StyleWithoutName())->enqueueCss();
    }

    public function testItEnqueueAStyleWithAName()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Functions\expect('wp_register_style')
            ->once()
            ->with('main', 'https://example.com/app/themes/theme/dist/css/app.css', [], null);
        Functions\expect('wp_enqueue_style')
            ->once()
            ->with('main');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new StyleWithName())->enqueueCss();
    }

    public function testItAddAVersionToEnqueuedStyle()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist', 'version' => '0.1.0']));

        Functions\expect('wp_register_style')
            ->once()
            ->with('main', 'https://example.com/app/themes/theme/dist/css/app.css', [], '0.1.0');
        Functions\expect('wp_enqueue_style')
            ->once()
            ->with('main');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new StyleWithVersion())->enqueueCss();
    }

    public function testItEnqueueARemoteStyle()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Functions\expect('wp_register_style')
            ->once()
            ->with('remote', 'https://remote.com/plugin/plugin.css');
        Functions\expect('wp_enqueue_style')
            ->once()
            ->with('remote');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new RemoteStyle())->enqueueCss();
    }

    public function testItEnqueueAStyleWithoutANameAndAConfigurationArray()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        $mockery = Mockery::mock(sprintf('alias:%s', Str::class));
        $mockery
            ->shouldReceive('uuid')
            ->once()
            ->andReturn('2e7caa8a-7013-44fe-a40b-83a59e53525d');

        Functions\expect('wp_register_style')
            ->once()
            ->with('2e7caa8a-7013-44fe-a40b-83a59e53525d', 'https://example.com/app/themes/theme/dist/css/app.css', [], null);
        Functions\expect('wp_enqueue_style')
            ->once()
            ->with('2e7caa8a-7013-44fe-a40b-83a59e53525d');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new StyleWithoutNameButConfiguration())->enqueueCss();
    }

    public function testItEnqueueAStyleWithANameAndAConfigurationArray()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Functions\expect('wp_register_style')
            ->once()
            ->with('main', 'https://example.com/app/themes/theme/dist/css/app.css', [], null);
        Functions\expect('wp_enqueue_style')
            ->once()
            ->with('main');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new StyleWithNameAndConfiguration())->enqueueCss();
    }

    public function testItThrowsAnExceptionIfThePathIsMissingInTheConfigurationWhenInjectingAStyle()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        $this->expectException(MissingPathException::class);

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new StyleWithoutPathInConfiguration())->enqueueCss();
    }

    public function testItNotEnqueueAStyle()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Functions\expect('wp_register_style')
            ->with('main', 'https://example.com/app/themes/theme/dist/css/app.css', [], null)
            ->never();
        Functions\expect('wp_enqueue_style')
            ->with('main')
            ->never();

        Functions\expect('wp_register_style')
            ->with('other', 'https://example.com/app/themes/theme/dist/css/other.css', [], null)
            ->once();
        Functions\expect('wp_enqueue_style')
            ->with('other')
            ->once();

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new DontEnqueueStyle())->enqueueCss();
    }

    public function testItNotEnqueueAnEmptyStylesArray()
    {
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Actions\expectAdded('wp_enqueue_scripts')
            ->never()
            ->whenHappen(fn ($callback) => $callback());

        (new EmptyStyle())->enqueueCss();
    }

    public function testItEnqueueAScriptWithoutAName()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        $mockery = Mockery::mock(sprintf('alias:%s', Str::class));
        $mockery
            ->shouldReceive('uuid')
            ->once()
            ->andReturn('ceca573e-4733-4f4e-a5e6-a01a66a9c5fe');

        Functions\expect('wp_register_script')
            ->once()
            ->with('ceca573e-4733-4f4e-a5e6-a01a66a9c5fe', 'https://example.com/app/themes/theme/dist/js/app.js', [], null, true);
        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('ceca573e-4733-4f4e-a5e6-a01a66a9c5fe');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new ScriptWithoutName())->enqueueJs();
    }

    public function testItEnqueueARemoteScript()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Functions\expect('wp_register_script')
            ->once()
            ->with('remote', 'https://remote.com/plugin/plugin.js');
        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('remote');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new RemoteScript())->enqueueJs();
    }

    public function testItEnqueueAScriptWithAName()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Functions\expect('wp_register_script')
            ->once()
            ->with('main', 'https://example.com/app/themes/theme/dist/js/app.js', [], null, true);
        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('main');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new ScriptWithName())->enqueueJs();
    }

    public function testItAddAVersionToEnqueuedScript()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist', 'version' => '0.1.0']));

        Functions\expect('wp_register_script')
            ->once()
            ->with('main', 'https://example.com/app/themes/theme/dist/js/app.js', [], '0.1.0', true);
        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('main');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new ScriptWithVersion())->enqueueJs();
    }

    public function testItNotEnqueueAScriptInTheFooter()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Functions\expect('wp_register_script')
            ->once()
            ->with('main', 'https://example.com/app/themes/theme/dist/js/app.js', [], null, false);
        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('main');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new ScriptNotInFooter())->enqueueJs();
    }

    public function testItEnqueueAScriptWithoutANameButHaveAConfigurationArray()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        $mockery = Mockery::mock(sprintf('alias:%s', Str::class));
        $mockery
            ->shouldReceive('uuid')
            ->once()
            ->andReturn('ceca573e-4733-4f4e-a5e6-a01a66a9c5fe');

        Functions\expect('wp_register_script')
            ->once()
            ->with('ceca573e-4733-4f4e-a5e6-a01a66a9c5fe', 'https://example.com/app/themes/theme/dist/js/app.js', [], null, true);
        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('ceca573e-4733-4f4e-a5e6-a01a66a9c5fe');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new ScriptWithoutNameButConfiguration())->enqueueJs();
    }

    public function testItEnqueueAScriptWithANameAndAConfigurationArray()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Functions\expect('wp_register_script')
            ->once()
            ->with('main', 'https://example.com/app/themes/theme/dist/js/app.js', [], null, true);
        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('main');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new ScriptWithNameAndConfiguration())->enqueueJs();
    }

    public function testItThrowsAnExceptionIfThePathIsMissingInTheConfigurationWhenInjectingAScript()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        $this->expectException(MissingPathException::class);

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new ScriptWithoutPathInConfiguration())->enqueueJs();
    }

    public function testItNotEnqueueAScript()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Functions\expect('wp_register_script')
            ->with('main', 'https://example.com/app/themes/theme/dist/js/app.js', [], null)
            ->never();
        Functions\expect('wp_enqueue_script')
            ->with('main')
            ->never();

        Functions\expect('wp_register_script')
            ->with('other', 'https://example.com/app/themes/theme/dist/js/other.js', [], null)
            ->once();
        Functions\expect('wp_enqueue_script')
            ->with('other')
            ->once();

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new DontEnqueueScript())->enqueueJs();
    }

    public function testItNotEnqueueAnEmptyScriptsArray()
    {
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Actions\expectAdded('wp_enqueue_scripts')
            ->never()
            ->whenHappen(fn ($callback) => $callback());

        (new EmptyScript())->enqueueJs();
    }

    public function testItEnqueueAScriptWithData()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        Functions\expect('wp_register_script')
            ->once()
            ->with('main', 'https://example.com/app/themes/theme/dist/js/app.js', [], null, true);
        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('main');
        Functions\expect('wp_localize_script')
            ->once()
            ->with('main', 'mainDatas', [
                'foo' => 'bar',
            ]);

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new ScriptWithData())->enqueueJs();
    }

    public function testItEnqueueAStyleWithAManifest()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist'], new Manifest(sprintf('%s/Fixtures/Style/manifest.json', __DIR__))));

        Functions\expect('wp_register_style')
            ->once()
            ->with('main', 'https://example.com/app/themes/theme/dist/css/app-5454564564654.css', [], null);
        Functions\expect('wp_enqueue_style')
            ->once()
            ->with('main');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new StyleWithName())->enqueueCss();
    }

    public function testItEnqueueAScriptWithAManifest()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist'], new Manifest(sprintf('%s/Fixtures/Script/manifest.json', __DIR__))));

        Functions\expect('wp_register_script')
            ->once()
            ->with('main', 'https://example.com/app/themes/theme/dist/js/app-21215478.js', [], null, true);
        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('main');

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        (new ScriptWithName())->enqueueJs();
    }

    /**
     * Handle `get_stylesheet_directory_uri` function to return
     * https://example.com
     */
    protected function handleGetStylesheetDirectoryUri()
    {
        Functions\when('get_stylesheet_directory_uri')->justReturn('https://example.com/app/themes/theme');
    }
}
