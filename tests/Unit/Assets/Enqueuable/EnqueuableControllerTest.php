<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable;

use themes\Wordpress\Framework\Core\Assets\Manager;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Controller\DontEnqueueController;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Controller\DontEnqueueWithoutEnqueuableController;
use themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Controller\EnqueuableController;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Functions;
use Rareloop\Lumberjack\Application;

/**
 * @preserveGlobalState disabled
 */
class EnqueuableControllerTest extends WordpressTestCase
{
    public function testItEnqueue()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        $mock = $this->getMockBuilder(EnqueuableController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())->method('enqueue');

        $reflectedController = new \ReflectionClass(EnqueuableController::class);
        $constructor = $reflectedController->getConstructor();
        $constructor->invoke($mock);
    }

    public function testItDontEnqueue()
    {
        $this->handleGetStylesheetDirectoryUri();
        $app = new Application();

        $app->bind('assets', new Manager(['path' => '/dist']));

        $mock = $this->getMockBuilder(DontEnqueueController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->never())->method('enqueue');

        $reflectedController = new \ReflectionClass(DontEnqueueController::class);
        $constructor = $reflectedController->getConstructor();
        $constructor->invoke($mock);
    }

    public function testItDontEnqueueIfTheEnqueableTraitIsMissingButThereIsAnEnqueueMethodInTheController()
    {
        $this->handleGetStylesheetDirectoryUri();

        $mock = $this->getMockBuilder(DontEnqueueWithoutEnqueuableController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->never())->method('enqueue');

        $reflectedController = new \ReflectionClass(DontEnqueueWithoutEnqueuableController::class);
        $constructor = $reflectedController->getConstructor();
        $constructor->invoke($mock);
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
