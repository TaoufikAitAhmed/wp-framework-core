<?php

namespace themes\Wordpress\Framework\Core\Test\Unit;

use themes\Wordpress\Framework\Core\Exceptions\TaxonomyRegistrationException;
use themes\Wordpress\Framework\Core\Term;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Functions;
use Illuminate\Support\Collection;
use Mockery;
use Timber\Timber;

/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */
class TermTest extends WordpressTestCase
{
    public function testRegisterFunctionCallsRegisterTaxonomyWhenTaxonomyTypeAndConfigAreProvided()
    {
        Functions\expect('register_taxonomy')
            ->once()
            ->with(
                RegisterableTaxonomyType::getTaxonomyType(),
                RegisterableTaxonomyType::getTaxonomyObjectTypes(),
                RegisterableTaxonomyType::getPrivateConfig(),
            );

        RegisterableTaxonomyType::register();
    }

    public function testRegisterFunctionThrowsExceptionIfTaxonomyTypeIsNotProvided()
    {
        $this->expectException(TaxonomyRegistrationException::class);
        UnregisterableTaxonomyWithoutTaxonomyType::register();
    }

    public function testRegisterFunctionThrowsExceptionIfConfigIsNotProvided()
    {
        $this->expectException(TaxonomyRegistrationException::class);
        UnregisterableTaxonomyWithoutConfig::register();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testQueryDefaultsToCurrentTaxonomyType()
    {
        $args = [
            'show_admin_column' => true,
        ];
        $maybe_args = [];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_terms')
            ->withArgs([
                array_merge($args, [
                    'taxonomy' => Term::getTaxonomyType(),
                ]),
                $maybe_args,
                Term::class,
            ])
            ->once();

        $terms = Term::query($args);

        $this->assertInstanceOf(Collection::class, $terms);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testQueryIgnoresPassedInTaxonomy()
    {
        $args = [
            'taxonomy'          => 'something-else',
            'show_admin_column' => true,
        ];
        $maybe_args = [];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_terms')
            ->withArgs([
                array_merge($args, [
                    'taxonomy'          => Term::getTaxonomyType(),
                    'show_admin_column' => true,
                ]),
                $maybe_args,
                Term::class,
            ])
            ->once();

        $terms = Term::query($args);

        $this->assertInstanceOf(Collection::class, $terms);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testTermSubclassQueryHasCorrectTaxonomyType()
    {
        $args = [
            'show_admin_column' => true,
        ];
        $maybe_args = [];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_terms')
            ->withArgs([
                Mockery::subset([
                    'taxonomy' => RegisterableTaxonomyType::getTaxonomyType(),
                ]),
                $maybe_args,
                RegisterableTaxonomyType::class,
            ])
            ->once();

        $terms = RegisterableTaxonomyType::query($args);

        $this->assertInstanceOf(Collection::class, $terms);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAllDefaultsToOrderedByTermOrderAscending()
    {
        $maybe_args = [];
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_terms')
            ->withArgs([
                Mockery::subset([
                    'orderby' => 'term_order',
                    'order'   => 'ASC',
                ]),
                $maybe_args,
                Term::class,
            ])
            ->once();

        $terms = Term::all();

        $this->assertInstanceOf(Collection::class, $terms);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAllCanHaveOrderSet()
    {
        $maybe_args = [];
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_terms')
            ->withArgs([
                Mockery::subset([
                    'orderby' => 'slug',
                    'order'   => 'DESC',
                ]),
                $maybe_args,
                Term::class,
            ])
            ->once();

        $terms = Term::all('slug', 'DESC');

        $this->assertInstanceOf(Collection::class, $terms);
    }

    public function testCanExtendTermBehaviourWithMacros()
    {
        Term::macro('testFunctionAddedByMacro', function () {
            return 'abc123';
        });

        $term = new Term(false, '', true);

        $this->assertSame('abc123', $term->testFunctionAddedByMacro());
        $this->assertSame('abc123', Term::testFunctionAddedByMacro());
    }

    public function testMacrosSetCorrectThisContextOnInstances()
    {
        Term::macro('testFunctionAddedByMacro', function () {
            return $this->dummyData();
        });

        $term = new Term(false, '', true);
        $term->dummyData = 'abc123';

        $this->assertSame('abc123', $term->testFunctionAddedByMacro());
    }

    public function testCanExtendTermBehaviourWithMixin()
    {
        Term::mixin(new TermMixin());

        $term = new Term(false, '', true);

        $this->assertSame('abc123', $term->testFunctionAddedByMixin());
    }
}

class TermMixin
{
    public function testFunctionAddedByMixin()
    {
        return function () {
            return 'abc123';
        };
    }
}

class RegisterableTaxonomyType extends Term
{
    public static function getTaxonomyType(): string
    {
        return 'registerable_taxonomy_type';
    }

    public static function getTaxonomyObjectTypes(): array
    {
        return ['post'];
    }

    public static function getPrivateConfig()
    {
        return self::getTaxonomyConfig();
    }

    protected static function getTaxonomyConfig(): array
    {
        return [
            'hierarchical'      => true,
            'labels'            => [
                'name'          => 'Tags',
                'singular_name' => 'Tag',
            ],
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [
                'slug' => 'the-tags',
            ],
        ];
    }
}

class UnregisterableTaxonomyWithoutTaxonomyType extends Term
{
    protected static function getTaxonomyConfig(): array
    {
        return [
            'labels'      => [
                'name'          => 'Groups',
                'singular_name' => 'Group',
            ],
            'public'      => true,
            'has_archive' => false,
            'supports'    => ['title', 'revisions'],
            'menu_icon'   => 'dashicons-groups',
            'rewrite'     => [
                'slug' => 'group',
            ],
        ];
    }
}

class UnregisterableTaxonomyWithoutConfig extends Term
{
    public static function getTaxonomyType(): string
    {
        return 'taxonomy_type';
    }
}
