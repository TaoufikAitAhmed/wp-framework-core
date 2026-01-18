<?php

namespace themes\Wordpress\Framework\Core\Database\Builders\Menu;

use themes\Wordpress\Framework\Core\Database\Builders\Menu\Contracts\MenuItemBuildable;
use Illuminate\Support\Collection;

class GroupMenuItemCollection extends Collection implements MenuItemBuildable
{
    protected static bool $allItemsClosed = true;

    /**
     * Is the group menu item close to modification ?
     *
     * @var bool
     */
    protected bool $close = false;

    /**
     * The parent menu item of the group item.
     *
     * @var MenuItem|null
     */
    protected ?MenuItem $menuItem = null;

    /**
     * Close the group menu item.
     *
     * @return $this
     */
    public function close(): self
    {
        $this->close = true;

        return $this;
    }

    /**
     * Open the group menu item.
     *
     * @return $this
     */
    public function open(): self
    {
        $this->close = false;

        return $this;
    }

    /**
     * Is the group menu item close ?
     *
     * @return bool
     */
    public function isClose(): bool
    {
        return $this->close;
    }

    /**
     * Is the group menu item open ?
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return !$this->isClose();
    }

    /**
     * Add the parent menu item to the group menu item.
     *
     * @param MenuItem $menuItem
     *
     * @return $this
     */
    public function addItem(MenuItem $menuItem): self
    {
        $this->menuItem = $menuItem;

        return $this;
    }

    /**
     * Get the parent menu item.
     *
     * @return MenuItem
     */
    public function getItem(): MenuItem
    {
        return $this->menuItem;
    }

    /**
     * Add a menu item to the sub menus.
     *
     * @param MenuItem $menuItem
     *
     * @return $this
     */
    public function addChildrenItem(MenuItem $menuItem): self
    {
        $this->push($menuItem);

        return $this;
    }

    /**
     * Add a children group menu item to the sub menus.
     *
     * @param GroupMenuItemCollection $groupMenuItems
     *
     * @return $this
     */
    public function addChildrenGroupMenuItem(self $groupMenuItems): self
    {
        $this->push($groupMenuItems);

        return $this;
    }

    /**
     * Build a group menu item collection.
     *
     * @param int|null $menuId
     * @param int|null $menuItemParentId
     *
     * @return void
     */
    public function build(?int $menuId = null, ?int $menuItemParentId = null)
    {
        $parentItem = $this->getItem()->build($menuId, $menuItemParentId);

        $this->map(function (MenuItemBuildable $item) use ($parentItem, $menuId) {
            $item->build($menuId, $parentItem);
        });
    }

    /**
     * Get the last opened group menu item.
     *
     * @return $this
     */
    public function getLastOpened(): self
    {
        if (!$this->isClose()) {
            return $this;
        }

        foreach ($this->getIterator() as $groupMenuItems) {
            if (is_a($groupMenuItems, self::class)) {
                return $this->getLastOpened();
            }
        }

        return $this;
    }

    /**
     * Are all items closed in the group menu items ?
     *
     * @return bool
     */
    public function areAllItemsClosed(): bool
    {
        self::$allItemsClosed = true;

        $this->map(function ($groupMenuItems) {
            if (is_a($groupMenuItems, self::class)) {
                if ($groupMenuItems->isOpen()) {
                    self::$allItemsClosed = false;
                }
            }
        });

        return self::$allItemsClosed;
    }
}
