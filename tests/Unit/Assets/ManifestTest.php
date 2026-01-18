<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets;

use themes\Wordpress\Framework\Core\Assets\Manifest;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;

/**
 * @preserveGlobalState disabled
 */
class ManifestTest extends WordpressTestCase
{
    public function testItCanReadAManifest()
    {
        $manifest = new Manifest(sprintf('%s/Fixtures/manifest.json', __DIR__));
        $this->assertSame('css/app-7253142354564564654.css', $manifest->asset('css/app.css'));
        $this->assertSame('js/app.js', $manifest->asset('js/app.js'));
    }

    public function testItCantReadAManifestIfAFileDoesNotExist()
    {
        $manifest = new Manifest('not-existing-file.json');
        $this->assertSame('css/app.css', $manifest->asset('css/app.css'));
        $this->assertSame('js/app.js', $manifest->asset('js/app.js'));
    }
}
