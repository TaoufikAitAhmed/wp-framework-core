<?php

namespace themes\Wordpress\Framework\Core\Database\Builders\Menu\Contracts;

interface MenuItemBuildable
{
    /**
     * Build an item.
     *
     * @param int|null $menuId           The menu ID.
     * @param int|null $menuItemParentId The parent menu of the menu if there is one.
     *
     * @return mixed
     */
    public function build(?int $menuId = null, ?int $menuItemParentId = null);
}
