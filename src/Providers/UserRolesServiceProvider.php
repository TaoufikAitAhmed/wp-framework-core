<?php

namespace themes\Wordpress\Framework\Core\Providers;

use WP_Admin_Bar;
use WP_User;

class UserRolesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        add_action('init', function () {
            add_role('doctor', __('Médecin', 'wp-framework-core'), [
                // TODO : Remplir
            ]);
            add_role('integrator', __('Intégrateur / Intégratrice', 'wp-framework-core'), [
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
            add_role(
                'head_integrator',
                __('Chef Intégrateur / Intégratrice', 'wp-framework-core'),
                [
                    'edit_theme_options'     => true,
                    'moderate_comments'      => true,
                    'manage_options'         => true,
                    'manage_categories'      => true,
                    'upload_files'           => true,
                    'edit_posts'             => true,
                    'edit_published_posts'   => true,
                    'edit_others_posts'      => true,
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

            if ($this->currentUserHaveRoles(['integrator'])) {
                // Remove page analysis columns from post lists, also SEO status on post editor
                add_filter('wpseo_use_page_analysis', '__return_false');
                // Remove Yoast meta boxes
                add_action('add_meta_boxes', function () {
                    remove_meta_box('wpseo_meta', 'post', 'normal');
                    remove_meta_box('wpseo_meta', 'page', 'normal');
                }, PHP_INT_MAX);
            }
        });

        if ($this->currentUserHaveRoles(['integrator'])) {
            add_action('admin_bar_menu', function (WP_Admin_Bar $wp_admin_bar) {
                $wp_admin_bar->remove_node('wpseo-menu');
                $wp_admin_bar->remove_node('wpseo-kwresearch');
                $wp_admin_bar->remove_node('wpseo-kwresearchtraining');
                $wp_admin_bar->remove_node('wpseo-adwordsexternal');
                $wp_admin_bar->remove_node('wpseo-googleinsights');
            }, PHP_INT_MAX);
        }

        add_action('admin_init', function () {
            if ($this->currentUserHaveRoles(['integrator'])) {
                remove_menu_page('wpseo_workouts');
                remove_menu_page('meowapps-main-menu');
                remove_submenu_page('themes.php', 'themes.php');
                remove_submenu_page('themes.php', 'customize.php?return=%2Fwp%2Fwp-admin%2F');
                remove_submenu_page('themes.php', 'customize.php?return=%2Fwp%2Fwp-admin%2Fnav-menus.php%3Faction%3Dedit%26menu%3D0');
                remove_submenu_page('tools.php', 'export-personal-data.php');
                remove_submenu_page('tools.php', 'erase-personal-data.php');
                remove_submenu_page('tools.php', 'tools.php');
                remove_submenu_page('options-general.php', 'options-general.php');
                remove_submenu_page('options-general.php', 'options-writing.php');
                remove_submenu_page('options-general.php', 'options-reading.php');
                remove_submenu_page('options-general.php', 'options-media.php');
                remove_submenu_page('options-general.php', 'options-permalink.php');
                remove_submenu_page('options-general.php', 'options-privacy.php');
                remove_submenu_page('options-general.php', 'duplicate_page_settings');
                remove_submenu_page('options-general.php', 'pc-robotstxt/admin.php');
                remove_submenu_page('options-general.php', 'imagify');
            }
            if ($this->currentUserHaveRoles(['head_integrator'])) {
                remove_menu_page('meowapps-main-menu');
            }
        }, PHP_INT_MAX);

        add_filter('acf/settings/show_admin', [$this, 'hideAcfAdminPage']);
    }

    /**
     * Should hide ACF Admin Page ?
     *
     * @return bool
     */
    public function hideAcfAdminPage(): bool
    {
        return $this->currentUserHaveRoles(['integrator', 'head_integrator']);
    }

    /**
     * Get current user.
     *
     * @return WP_User|null
     */
    protected function getCurrentUser(): ?WP_User
    {
        return wp_get_current_user();
    }

    /**
     * Do the current user have roles ?
     *
     * @param array $roles
     *
     * @return bool
     */
    protected function currentUserHaveRoles(array $roles): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        foreach ($roles as $role) {
            if (in_array($role, $user->roles)) {
                return true;
            }
        }

        return false;
    }
}
