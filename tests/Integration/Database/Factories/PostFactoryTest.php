<?php

namespace themes\Wordpress\Framework\Core\Test\Integration;

use themes\Wordpress\Framework\Core\Database\Factories\PostFactory;
use Rareloop\Lumberjack\Post;
use Timber\Image;

class PostFactoryTest extends IntegrationTestCase
{
    public function test_it_can_create_a_post()
    {
        $post = PostFactory::new()->make([
            'post_meta' => [
                'foo'  => 'bar',
                'john' => 'doe',
            ],
        ]);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertNotEmpty($post->post_type);
        $this->assertEquals('post', $post->post_type);
        $this->assertNotEmpty($post->post_status);
        $this->assertEquals('publish', $post->post_status);
        $this->assertNotEmpty($post->post_name);
        $this->assertNotEmpty($post->post_title);
        $this->assertNotEmpty($post->post_date);
        $this->assertNotEmpty($post->post_date_gmt);
        $this->assertNotEmpty($post->post_modified);
        $this->assertNotEmpty($post->post_modified_gmt);
        $this->assertNotEmpty($post->post_content);
        $this->assertNotEmpty($post->post_excerpt);
        $this->assertInstanceOf(Image::class, $post->thumbnail());
        $this->assertTrue((bool) $post->thumbnail()->meta('_wp-framework-core-fake'));
        $this->assertTrue((bool) $post->meta('_wp-framework-core-fake'));
        $this->assertEquals('bar', $post->meta('foo'));
        $this->assertEquals('doe', $post->meta('john'));
    }
}
