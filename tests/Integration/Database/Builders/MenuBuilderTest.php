<?php

namespace themes\Wordpress\Framework\Core\Test\Integration;

use themes\Wordpress\Framework\Core\Database\Builders\Menu\Exceptions\LocationDoesNotExistsException;
use themes\Wordpress\Framework\Core\Database\Builders\Menu\Exceptions\MenuAlreadyExistsException;
use themes\Wordpress\Framework\Core\Database\Builders\Menu\MenuBuilder;
use Timber\Menu;
use Timber\MenuItem;
use WP_Term;

class MenuBuilderTest extends IntegrationTestCase
{
    public function test_it_can_create_a_menu_with_a_name()
    {
        $this->assertFalse(wp_get_nav_menu_object('Foo'));

        $menuBuilder = new MenuBuilder('Foo');

        $menuBuilder->build();

        $this->assertNotNull(wp_get_nav_menu_object('Foo'));
        $this->assertInstanceOf(WP_Term::class, wp_get_nav_menu_object('Foo'));
    }

    public function test_it_can_create_a_menu_with_a_location()
    {
        register_nav_menus([
            'bar' => 'bar',
        ]);

        $menuBuilder = new MenuBuilder('Foo', 'bar');

        $menuBuilder->build();

        $menu = new Menu('Foo');
        $this->assertEquals('bar', $menu->theme_location);
    }

    public function test_if_the_menu_already_exists_it_returns_an_exception()
    {
        $menuBuilder = new MenuBuilder('Foo');
        $menuBuilder->build();

        $this->expectException(MenuAlreadyExistsException::class);
        $this->expectExceptionMessage('The menu Foo already exists.');

        $menuBuilder->build();
    }

    public function test_if_the_location_does_not_exist_it_returns_an_exception()
    {
        $menuBuilder = new MenuBuilder('Foo', 'baz');

        $this->expectException(LocationDoesNotExistsException::class);
        $this->expectExceptionMessage('The location baz does not exists.');

        $menuBuilder->build();
    }

    public function test_it_can_add_items_to_a_menu()
    {
        $menuBuilder = new MenuBuilder('Foo');

        //@formatter:off
        $menuBuilder
            ->addItem('My first item')
            ->addItem('My second item')
            ->addItem('My third item');
        //@formatter:on

        $menuBuilder->build();

        $menu = new Menu('foo');
        /** @var MenuItem[] $items */
        $items = $menu->get_items();

        $this->assertCount(3, $items);

        $this->assertEquals('My first item', $items[0]->title());
        $this->assertEquals('My second item', $items[1]->title());
        $this->assertEquals('My third item', $items[2]->title());
    }

    public function test_it_can_create_a_simple_menu()
    {
        $menuBuilder = new MenuBuilder('Foo');

        //@formatter:off
        $menuBuilder
            ->addItem('First item')
            ->addGroupItem('Second item')
                ->addItem('First child of second item')
                ->addItem('Second child of second item')
            ->endGroupItem()
            ->addItem('Third item');
        //@formatter:on

        $menuBuilder->build();

        $menu = new Menu('foo');
        /** @var MenuItem[] $items */
        $items = $menu->get_items();

        $this->assertCount(3, $items);

        $this->assertEquals('First item', $items[0]->title());
        $this->assertEquals('Second item', $items[1]->title());
        $this->assertEquals('First child of second item', $items[1]->children()[0]->title());
        $this->assertEquals('Second child of second item', $items[1]->children()[1]->title());
        $this->assertEquals('Third item', $items[2]->title());
    }

    public function test_it_can_add_multiple_sub_items_to_a_menu()
    {
        $menuBuilder = new MenuBuilder('Foo');

        //@formatter:off
        $menuBuilder
            ->addGroupItem('My first item')
                ->addGroupItem('My first sub item of my first item')
                    ->addItem('My first sub item of my sub item of my first item')
                    ->addItem('My second sub item of my sub item of my first item')
                ->endGroupItem()
                ->addItem('My second sub item of my first item')
                ->addGroupItem('My third sub item of my first item')
                    ->addItem('My first sub item of my third sub item of my first item')
                    ->addGroupItem('My second sub item of my third sub item of my first item')
                        ->addItem('My first sub item of my second sub item of my third sub item of my first item')
                    ->endGroupItem()
                    ->addGroupItem('My third sub item of my third sub item of my first item')
                        ->addItem('My first sub item of my third sub item of my third sub item of my first item')
                    ->endGroupItem()
                ->endGroupItem()
            ->endGroupItem()
            ->addItem('My second item')
            ->addGroupItem('My third item')
                ->addItem('My first sub menu of my third item')
            ->endGroupItem();
        //@formatter:on

        $menuBuilder->build();

        $menu = new Menu('foo');
        /** @var MenuItem[] $items */
        $items = $menu->get_items();

        $this->assertCount(3, $items);

        $this->assertEquals('My first item', $items[0]->title());
        $this->assertEquals('My first sub item of my first item', $items[0]->children()[0]->title());
        $this->assertEquals('My first sub item of my sub item of my first item', $items[0]->children()[0]->children()[0]->title());
        $this->assertEquals('My second sub item of my sub item of my first item', $items[0]->children()[0]->children()[1]->title());
        $this->assertEquals('My second sub item of my first item', $items[0]->children()[1]->title());
        $this->assertEquals('My third sub item of my first item', $items[0]->children()[2]->title());
        $this->assertEquals('My first sub item of my third sub item of my first item', $items[0]->children()[2]->children()[0]->title());
        $this->assertEquals('My second sub item of my third sub item of my first item', $items[0]->children()[2]->children()[1]->title());
        $this->assertEquals(
            'My first sub item of my second sub item of my third sub item of my first item',
            $items[0]->children()[2]->children()[1]->children()[0]->title()
        );
        $this->assertEquals('My third sub item of my third sub item of my first item', $items[0]->children()[2]->children()[2]->title());
        $this->assertEquals(
            'My first sub item of my third sub item of my third sub item of my first item',
            $items[0]->children()[2]->children()[2]->children()[0]->title()
        );
        $this->assertEquals('My second item', $items[1]->title());
        $this->assertEquals('My third item', $items[2]->title());
        $this->assertEquals('My first sub menu of my third item', $items[2]->children()[0]->title());
    }
}
