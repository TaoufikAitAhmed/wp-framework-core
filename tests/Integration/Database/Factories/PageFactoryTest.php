<?php

namespace themes\Wordpress\Framework\Core\Test\Integration;

use themes\Wordpress\Framework\Core\Database\Factories\PageFactory;
use Rareloop\Lumberjack\Page;

class PageFactoryTest extends IntegrationTestCase
{
    public function test_it_can_create_a_page()
    {
        $page = PageFactory::new()->make([
            'page_meta' => [
                'foo'  => 'bar',
                'john' => 'doe',
            ],
        ]);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertNotEmpty($page->post_type);
        $this->assertEquals('page', $page->post_type);
        $this->assertNotEmpty($page->post_status);
        $this->assertEquals('publish', $page->post_status);
        $this->assertNotEmpty($page->post_name);
        $this->assertNotEmpty($page->post_title);
        $this->assertNotEmpty($page->post_date);
        $this->assertNotEmpty($page->post_date_gmt);
        $this->assertNotEmpty($page->post_modified);
        $this->assertNotEmpty($page->post_modified_gmt);
        $this->assertNotEmpty($page->post_content);
        $this->assertTrue((bool) $page->meta('_wp-framework-core-fake'));
        $this->assertEquals('bar', $page->meta('foo'));
        $this->assertEquals('doe', $page->meta('john'));
    }

    public function test_it_can_create_a_page_with_a_title()
    {
        $page = PageFactory::new()
                           ->setTitle('foo bar')
                           ->make();

        $this->assertEquals('foo bar', $page->post_title);
        $this->assertEquals('foo-bar', $page->post_name);
    }
}
