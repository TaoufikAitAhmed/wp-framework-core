<?php

namespace themes\Wordpress\Framework\Core\Database\Builders\Menu;

use themes\Wordpress\Framework\Core\Database\Builders\Menu\Contracts\MenuItemBuildable;
use WP_Error;

class MenuItem implements MenuItemBuildable
{
    /**
     * Name of the menu item.
     *
     * @var string
     */
    protected string $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Build a menu item.
     *
     * @param int|null $menuId           The menu ID.
     * @param int|null $menuItemParentId The parent menu of the menu if there is one.
     *
     * @return int|WP_Error
     */
    public function build(?int $menuId = null, ?int $menuItemParentId = null)
    {
        $menuItemData = [
            'menu-item-title'  => $this->name,
            'menu-item-url'    => '#',
            'menu-item-status' => 'publish',
        ];

        if (isset($menuItemParentId) && $menuItemParentId) {
            $menuItemData['menu-item-parent-id'] = $menuItemParentId;
        }

        return wp_update_nav_menu_item($menuId, 0, $menuItemData);
    }
}
