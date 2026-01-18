<?php

namespace themes\Wordpress\Framework\Core\Test\Integration;

use themes\Wordpress\Framework\Core\Database\Factories\TermFactory;
use themes\Wordpress\Framework\Core\Term;

class TermFactoryTest extends IntegrationTestCase
{
    public function test_it_can_create_a_term()
    {
        $term = TermFactory::new()->make([
            'term_meta' => [
                'foo'  => 'bar',
                'john' => 'doe',
            ],
        ]);

        $this->assertInstanceOf(Term::class, $term);
        $this->assertNotEmpty($term->name);
        $this->assertNotEmpty($term->slug);
        $this->assertNotEmpty($term->taxonomy);
        $this->assertNotEmpty($term->description);
        $this->assertTrue((bool) $term->meta('_wp-framework-core-fake'));
        $this->assertEquals('bar', $term->meta('foo'));
        $this->assertEquals('doe', $term->meta('john'));
    }
}
