<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Providers;

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Providers\UserRolesServiceProvider;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use phpmock\MockBuilder;

/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */
class UserRolesServiceProviderTest extends WordpressTestCase
{
    /**
     * Others roles (except integrator).
     *
     * @var array|string[]
     */
    protected array $othersRoles = [
        'head_integrator',
        'administrator',
        'wpseo_manager',
        'wpseo_editor',
    ];

    private \phpmock\Mock $mockLanguage;

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_user_roles_are_created()
    {
        Functions\when('wp_get_current_user')->justReturn();

        Functions\expect('add_role')
            ->once()
            ->with('doctor', 'Médecin', [
                // TODO : Remplir
            ]);

        Functions\expect('add_role')
            ->once()
            ->with('integrator', 'Intégrateur / Intégratrice', [
                'edit_theme_options'     => true,
                'moderate_comments'      => true,
                'manage_options'         => true,
                'manage_categories'      => true,
                'upload_files'           => true,
                'edit_posts'             => true,
                'edit_others_posts'      => true,
                'edit_published_posts'   => true,
                'publish_posts'          => true,
                'edit_pages'             => true,
                'read'                   => true,
                'publish_pages'          => true,
                'edit_published_pages'   => true,
                'delete_pages'           => true,
                'edit_others_pages'      => true,
                'delete_others_page'     => true,
                'delete_published_pages' => true,
                'delete_posts'           => true,
                'delete_others_posts'    => true,
                'delete_published_posts' => true,
                'delete_private_posts'   => true,
                'switch_themes'          => false,
                'edit_themes'            => false,
                'edit_private_posts'     => true,
                'read_private_posts'     => true,
                'delete_private_pages'   => true,
                'edit_private_pages'     => true,
                'read_private_pages'     => true,
                'delete_users'           => false,
                'create_users'           => false,
                'list_users'             => false,
                'unfiltered_html'        => true,
            ]);

        Functions\expect('add_role')
            ->once()
            ->with(
                'head_integrator',
                'Chef Intégrateur / Intégratrice',
                [
                    'edit_theme_options'     => true,
                    'moderate_comments'      => true,
                    'manage_options'         => true,
                    'manage_categories'      => true,
                    'upload_files'           => true,
                    'edit_posts'             => true,
                    'edit_others_posts'      => true,
                    'edit_published_posts'   => true,
                    'publish_posts'          => true,
                    'edit_pages'             => true,
                    'read'                   => true,
                    'publish_pages'          => true,
                    'edit_published_pages'   => true,
                    'edit_others_pages'      => true,
                    'delete_pages'           => true,
                    'delete_others_page'     => true,
                    'delete_published_pages' => true,
                    'delete_posts'           => true,
                    'delete_others_posts'    => true,
                    'delete_published_posts' => true,
                    'delete_private_posts'   => true,
                    'switch_themes'          => false,
                    'edit_themes'            => false,
                    'edit_private_posts'     => true,
                    'read_private_posts'     => true,
                    'delete_private_pages'   => true,
                    'edit_private_pages'     => true,
                    'read_private_pages'     => true,
                    'delete_users'           => true,
                    'create_users'           => true,
                    'list_users'             => true,
                    'unfiltered_html'        => true,
                ]
            );

        $app = new Application(__DIR__ . '/../');

        Actions\expectAdded('init')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $provider = new UserRolesServiceProvider($app);
        $provider->boot();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_integrator_role_do_not_have_seo_meta_boxes()
    {
        $this->mockWpUserRole(['integrator']);

        Functions\when('add_role')->justReturn();

        Filters\expectAdded('wpseo_use_page_analysis')
            ->with('__return_false')
            ->once();

        Functions\expect('remove_meta_box')
            ->once()
            ->with('wpseo_meta', 'post', 'normal');

        Functions\expect('remove_meta_box')
            ->once()
            ->with('wpseo_meta', 'page', 'normal');

        Actions\expectAdded('add_meta_boxes')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        Actions\expectAdded('init')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $app = new Application(__DIR__ . '/../');

        $provider = new UserRolesServiceProvider($app);
        $provider->boot();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_other_roles_do_have_seo_meta_boxes()
    {
        $this->mockWpUserRole($this->othersRoles);

        Functions\when('add_role')->justReturn();

        Filters\expectAdded('wpseo_use_page_analysis')
            ->never();

        Functions\expect('remove_meta_box')
            ->never()
            ->with('wpseo_meta', 'post', 'normal');

        Functions\expect('remove_meta_box')
            ->never()
            ->with('wpseo_meta', 'page', 'normal');

        Actions\expectAdded('add_meta_boxes')
            ->never()
            ->whenHappen(fn ($callback) => $callback());

        Actions\expectAdded('init')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $app = new Application(__DIR__ . '/../');

        $provider = new UserRolesServiceProvider($app);
        $provider->boot();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_integrator_role_do_not_have_useless_admin_bar_items()
    {
        $this->mockWpUserRole(['integrator']);

        $mockWpAdminBar = \Mockery::mock(\WP_Admin_Bar::class);

        $mockWpAdminBar
            ->shouldReceive('remove_node')
            ->once()
            ->with('wpseo-menu');

        $mockWpAdminBar
            ->shouldReceive('remove_node')
            ->once()
            ->with('wpseo-kwresearch');

        $mockWpAdminBar
            ->shouldReceive('remove_node')
            ->once()
            ->with('wpseo-kwresearchtraining');

        $mockWpAdminBar
            ->shouldReceive('remove_node')
            ->once()
            ->with('wpseo-adwordsexternal');

        $mockWpAdminBar
            ->shouldReceive('remove_node')
            ->once()
            ->with('wpseo-googleinsights');

        Actions\expectAdded('admin_bar_menu')
            ->once()
            ->whenHappen(fn ($callback) => $callback($mockWpAdminBar));

        $app = new Application(__DIR__ . '/../');

        $provider = new UserRolesServiceProvider($app);
        $provider->boot();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_other_roles_do_have_admin_bar_items_integrator_does_not_have()
    {
        $this->mockWpUserRole($this->othersRoles);

        $mockWpAdminBar = \Mockery::mock(\WP_Admin_Bar::class);

        $mockWpAdminBar
            ->shouldReceive('remove_node')
            ->never()
            ->with('wpseo-menu');

        $mockWpAdminBar
            ->shouldReceive('remove_node')
            ->never()
            ->with('wpseo-kwresearch');

        $mockWpAdminBar
            ->shouldReceive('remove_node')
            ->never()
            ->with('wpseo-kwresearchtraining');

        $mockWpAdminBar
            ->shouldReceive('remove_node')
            ->never()
            ->with('wpseo-adwordsexternal');

        $mockWpAdminBar
            ->shouldReceive('remove_node')
            ->never()
            ->with('wpseo-googleinsights');

        Actions\expectAdded('admin_bar_menu')
            ->never();

        $app = new Application(__DIR__ . '/../');

        $provider = new UserRolesServiceProvider($app);
        $provider->boot();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_integrator_role_do_not_have_useless_menu_and_sub_menu_page()
    {
        $this->mockWpUserRole(['integrator']);

        Functions\expect('remove_menu_page')
            ->once()
            ->with('wpseo_workouts');
        Functions\expect('remove_menu_page')
            ->once()
            ->with('meowapps-main-menu');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('themes.php', 'themes.php');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('themes.php', 'customize.php?return=%2Fwp%2Fwp-admin%2F');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('themes.php', 'customize.php?return=%2Fwp%2Fwp-admin%2Fnav-menus.php%3Faction%3Dedit%26menu%3D0');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('tools.php', 'export-personal-data.php');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('tools.php', 'erase-personal-data.php');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('tools.php', 'tools.php');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('options-general.php', 'options-general.php');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('options-general.php', 'options-writing.php');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('options-general.php', 'options-reading.php');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('options-general.php', 'options-media.php');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('options-general.php', 'options-permalink.php');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('options-general.php', 'options-privacy.php');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('options-general.php', 'duplicate_page_settings');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('options-general.php', 'pc-robotstxt/admin.php');
        Functions\expect('remove_submenu_page')
            ->once()
            ->with('options-general.php', 'imagify');

        Actions\expectAdded('admin_init')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $app = new Application(__DIR__ . '/../');

        $provider = new UserRolesServiceProvider($app);
        $provider->boot();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_others_roles_do_have_menu_and_sub_menu_page_integrator_does_not_have()
    {
        $this->mockWpUserRole($this->othersRoles);

        Functions\expect('remove_menu_page')
            ->never()
            ->with('wpseo_workouts');

        Functions\expect('remove_menu_page')
            ->never()
            ->with('wpseo_workouts');

        Functions\expect('remove_submenu_page')
            ->never()
            ->with('themes.php', 'themes.php');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('themes.php', 'customize.php?return=%2Fwp%2Fwp-admin%2F');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('themes.php', 'customize.php?return=%2Fwp%2Fwp-admin%2Fnav-menus.php%3Faction%3Dedit%26menu%3D0');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('tools.php', 'export-personal-data.php');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('tools.php', 'erase-personal-data.php');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('tools.php', 'tools.php');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('options-general.php', 'options-general.php');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('options-general.php', 'options-writing.php');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('options-general.php', 'options-reading.php');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('options-general.php', 'options-media.php');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('options-general.php', 'options-permalink.php');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('options-general.php', 'options-privacy.php');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('options-general.php', 'duplicate_page_settings');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('options-general.php', 'pc-robotstxt/admin.php');
        Functions\expect('remove_submenu_page')
            ->never()
            ->with('options-general.php', 'imagify');

        Actions\expectAdded('admin_init')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $app = new Application(__DIR__ . '/../');

        $provider = new UserRolesServiceProvider($app);
        $provider->boot();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_head_integrator_role_do_not_have_useless_menu_and_sub_menu_page()
    {
        $this->mockWpUserRole(['head_integrator']);

        Functions\expect('remove_menu_page')
            ->once()
            ->with('meowapps-main-menu');

        Actions\expectAdded('admin_init')
            ->once()
            ->whenHappen(fn ($callback) => $callback());

        $app = new Application(__DIR__ . '/../');

        $provider = new UserRolesServiceProvider($app);
        $provider->boot();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_head_integrator_role_do_not_have_acf_page_in_admin()
    {
        $app = new Application(__DIR__ . '/../');
        $provider = new UserRolesServiceProvider($app);

        $this->mockWpUserRole(['head_integrator']);

        $this->assertTrue($provider->hideAcfAdminPage());

        Filters\expectAdded('acf/settings/show_admin')
            ->once()
            ->with([$provider, 'hideAcfAdminPage']);

        $provider->boot();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_integrator_role_do_not_have_acf_page_in_admin()
    {
        $app = new Application(__DIR__ . '/../');
        $provider = new UserRolesServiceProvider($app);

        $this->mockWpUserRole(['integrator']);

        $this->assertTrue($provider->hideAcfAdminPage());

        Filters\expectAdded('acf/settings/show_admin')
            ->once()
            ->with([$provider, 'hideAcfAdminPage']);

        $provider->boot();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_administrator_role_do_have_acf_page_in_admin()
    {
        $app = new Application(__DIR__ . '/../');
        $provider = new UserRolesServiceProvider($app);

        $this->mockWpUserRole(['administrator']);

        $this->assertFalse($provider->hideAcfAdminPage());

        Filters\expectAdded('acf/settings/show_admin')
            ->once()
            ->with([$provider, 'hideAcfAdminPage']);

        $provider->boot();
    }

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->mockWordPressLanguageFunctions();
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        $this->mockLanguage->disable();
    }

    /**
     * Mock WP User with roles.
     *
     * @param array $roles
     *
     * @return \Mockery\LegacyMockInterface|\Mockery\MockInterface|\WP_User
     */
    protected function mockWpUserRole(array $roles)
    {
        $mockWpUser = \Mockery::mock(\WP_User::class);
        $mockWpUser->roles = $roles;

        Functions\when('wp_get_current_user')
            ->justReturn($mockWpUser);

        return $mockWpUser;
    }

    protected function mockWordPressLanguageFunctions(string $namespace = 'themes\Wordpress\Framework\Core\Providers')
    {
        $builder = new MockBuilder();
        $builder
            ->setNamespace($namespace)
            ->setName('__')
            ->setFunction(fn ($input) => $input);

        $mock = $builder->build();
        $mock->enable();
        $this->mockLanguage = $mock;
    }
}
