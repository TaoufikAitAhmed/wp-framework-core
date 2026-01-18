<?php

namespace themes\Wordpress\Framework\Core\Test\Unit;

use themes\Wordpress\Framework\Core\Acf\Options as Options;
use themes\Wordpress\Framework\Core\Application;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Post as BasePostType;

/**
 * @preserveGlobalState disabled
 */
class OptionsTest extends TestCase
{
    public function test_parent_can_be_a_post_type()
    {
        $app = new Application();

        Functions\when('acf_add_options_page')
            ->justReturn(true);

        Functions\when('acf_add_local_field_group')
            ->justReturn(true);

        Functions\when('get_the_ID')
            ->justReturn(true);

        Filters\expectAdded('init')->withAnyArgs();

        $optionPage = new OptionPage($app);
        $optionPage->compose();

        $this->assertEquals('edit.php?post_type=posttype', $optionPage->parent);
    }

    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        \Brain\Monkey\tearDown();
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
