<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Providers;

use themes\Wordpress\Framework\Core\Acf\Options as Options;
use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Providers\AcfServiceProvider;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Rareloop\Lumberjack\Post as BasePostType;

/**
 * @preserveGlobalState disabled
 * @runTestsInSeparateProcesses
 */
class AcfServiceProviderTest extends WordpressTestCase
{
    protected AcfServiceProvider $provider;

    protected Application $app;

    public function test_loading_options_page_throws_an_exception_if_name_occurs_twice()
    {
        $this->provider->acfOptions = new Collection([
            new OptionPage($this->app),
            new OptionPage2($this->app),
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The name 'Option Page' is used several times for option pages. Please specify a unique name for each option page.");

        $this->provider->loadAcfOptions();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Application();
        $this->provider = new AcfServiceProvider($this->app);
    }
}

class OptionPage extends Options
{
    /**
     * The option page menu name.
     *
     * @var string
     */
    public string $name = 'Option Page';

    /**
     * The option page document title.
     *
     * @var string
     */
    public string $title = 'Option Page | Options';

    /**
     * The option page permission capability.
     *
     * @var string
     */
    public string $capability = 'manage_options';

    /**
     * The slug of another admin page to be used as a parent.
     *
     * @var string|null
     */
    public ?string $parent = PostType::class;

    /**
     * The field group.
     *
     * @return array
     * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
     */
    public function fields(): array
    {
        return [];
    }
}

class OptionPage2 extends Options
{
    /**
     * The option page menu name.
     *
     * @var string
     */
    public string $name = 'Option Page';

    /**
     * The option page document title.
     *
     * @var string
     */
    public string $title = 'Option Page | Options';

    /**
     * The option page permission capability.
     *
     * @var string
     */
    public string $capability = 'manage_options';

    /**
     * The slug of another admin page to be used as a parent.
     *
     * @var string|null
     */
    public ?string $parent = PostType2::class;

    /**
     * The field group.
     *
     * @return array
     * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
     */
    public function fields(): array
    {
        return [];
    }
}

class PostType extends BasePostType
{
    /**
     * Return the key used to register the post type with WordPress
     * First parameter of the `register_post_type` function:
     * https://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @return string
     */
    public static function getPostType()
    {
        return 'posttype';
    }

    /**
     * Return the config to use to register the post type with WordPress
     * Second parameter of the `register_post_type` function:
     * https://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @return array
     */
    protected static function getPostTypeConfig()
    {
        return [];
    }
}

class PostType2 extends BasePostType
{
    /**
     * Return the key used to register the post type with WordPress
     * First parameter of the `register_post_type` function:
     * https://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @return string
     */
    public static function getPostType()
    {
        return 'posttype2';
    }

    /**
     * Return the config to use to register the post type with WordPress
     * Second parameter of the `register_post_type` function:
     * https://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @return array
     */
    protected static function getPostTypeConfig()
    {
        return [];
    }
}
