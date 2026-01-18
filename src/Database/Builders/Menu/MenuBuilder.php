<?php

namespace themes\Wordpress\Framework\Core\Database\Builders\Menu;

use themes\Wordpress\Framework\Core\Database\Builders\Menu\Contracts\MenuItemBuildable;
use themes\Wordpress\Framework\Core\Database\Builders\Menu\Exceptions\LocationDoesNotExistsException;
use themes\Wordpress\Framework\Core\Database\Builders\Menu\Exceptions\MenuAlreadyExistsException;
use Illuminate\Support\Collection;

final class MenuBuilder
{
    /**
     * All closed group menu items.
     *
     * @var array
     */
    protected static array $closedGroupMenuItems = [];

    /**
     * The very first group menu items being instantiated.
     *
     * @var GroupMenuItemCollection|null
     */
    protected static ?GroupMenuItemCollection $firstGroupMenuItems = null;

    /**
     * Name of the menu.
     *
     * @var string
     */
    protected string $name;

    /**
     * Location of the menu.
     *
     * @var string|null
     */
    protected ?string $location = null;

    /**
     * All items of the menu.
     *
     * This is a collection of :
     *
     * - MenuItem
     * - GroupMenuItemCollection
     *
     * @var Collection
     */
    protected Collection $items;

    /**
     * Represents the active group menu item.
     *
     * @var GroupMenuItemCollection|null
     */
    protected ?GroupMenuItemCollection $groupMenuItems = null;

    /**
     * @param string      $name
     * @param string|null $location
     */
    public function __construct(string $name, ?string $location = null)
    {
        $this->name = $name;
        $this->location = $location;
        $this->items = new Collection();
    }

    /**
     * Build a menu.
     *
     * @return void
     * @throws MenuAlreadyExistsException
     * @throws LocationDoesNotExistsException
     */
    public function build(): void
    {
        // Create the menu.
        $menuId = wp_create_nav_menu($this->name);

        if (is_wp_error($menuId)) {
            throw new MenuAlreadyExistsException("The menu {$this->name} already exists.");
        }

        update_term_meta($menuId, '_wp-framework-core-fake', true);

        // Set the location of the menu.
        if (isset($this->location)) {
            if (!array_key_exists($this->location, get_registered_nav_menus())) {
                throw new LocationDoesNotExistsException("The location {$this->location} does not exists.");
            }

            set_theme_mod('nav_menu_locations', [
                $this->location => $menuId,
            ]);
        }

        // Build the menu with inner items.
        $this->items->map(function (MenuItemBuildable $item) use ($menuId) {
            $item->build($menuId);
        });
    }

    /**
     * Add an item to the menu.
     *
     * @param string $name The name of the item.
     *
     * @return $this
     */
    public function addItem(string $name): self
    {
        $menuItem = $this->createMenuItem($name);

        if (!$this->groupMenuItems) {
            $this->items->push($menuItem);

            return $this;
        }

        if (!$this->groupMenuItems->areAllItemsClosed()) {
            $this->getLastClosedGroupMenuItems()->addChildrenItem($menuItem);

            return $this;
        }

        $this->groupMenuItems->push($menuItem);

        return $this;
    }

    /**
     * Add a group item to the menu.
     *
     * @param string $name The name of the menu item to add as a "parent" for the group menu item.
     *
     * @return $this
     */
    public function addGroupItem(string $name): self
    {
        $newGroupMenuItems = (new GroupMenuItemCollection())
            ->addItem($this->createMenuItem($name));

        if (!$this->groupMenuItems) {
            self::$firstGroupMenuItems = $newGroupMenuItems;
            $this->groupMenuItems = $newGroupMenuItems;

            return $this;
        }

        if ($this->countClosedGroupMenuItems() > 0) {
            $this
                ->getLastClosedGroupMenuItems()
                ->addChildrenGroupMenuItem($newGroupMenuItems);
            $this->addToClosedGroupMenuItems($newGroupMenuItems);

            return $this;
        }

        $lastOpened = $this
            ->groupMenuItems
            ->getLastOpened()
            ->close();
        $this->addToClosedGroupMenuItems($lastOpened);
        $this->addToClosedGroupMenuItems($newGroupMenuItems);

        $this
            ->groupMenuItems
            ->getLastOpened()
            ->addChildrenGroupMenuItem($newGroupMenuItems);

        return $this;
    }

    /**
     * End a group item.
     *
     * @return $this
     */
    public function endGroupItem(): self
    {
        if ($this->countClosedGroupMenuItems() === 1 || $this->countClosedGroupMenuItems() === 0) {
            $this->items->push($this->groupMenuItems);
            $this->groupMenuItems = null;

            return $this;
        }

        $this
            ->getLastClosedGroupMenuItems()
            ->close();
        $this->removeLastClosedGroupMenuItems();

        return $this;
    }

    /**
     * Add a group menu item to the array containing all closed group items.
     *
     * @param GroupMenuItemCollection $groupMenuItems
     *
     * @return void
     */
    protected function addToClosedGroupMenuItems(GroupMenuItemCollection $groupMenuItems): void
    {
        self::$closedGroupMenuItems[] = $groupMenuItems;
    }

    /**
     * Remove the last closed group menu items.
     *
     * @return void
     */
    protected function removeLastClosedGroupMenuItems(): void
    {
        array_pop(self::$closedGroupMenuItems);
    }

    /**
     * Count the number of closed group menu items.
     *
     * @return int
     */
    protected function countClosedGroupMenuItems(): int
    {
        return count(self::$closedGroupMenuItems);
    }

    /**
     * Create a menu item.
     *
     * @param string $name The name of the item.
     *
     * @return MenuItem
     */
    protected function createMenuItem(string $name): MenuItem
    {
        return new MenuItem($name);
    }

    /**
     * Get the last closed group menu items.
     *
     * @return GroupMenuItemCollection
     */
    protected function getLastClosedGroupMenuItems(): GroupMenuItemCollection
    {
        return end(self::$closedGroupMenuItems);
    }
}
