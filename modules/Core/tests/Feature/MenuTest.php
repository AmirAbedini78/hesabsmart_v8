<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

namespace Modules\Core\Tests\Feature;

use Modules\Core\Facades\Menu;
use Modules\Core\Menu\MenuItem;
use Tests\TestCase;

class MenuTest extends TestCase
{
    public function test_menu_item_can_be_added(): void
    {
        Menu::clear();
        Menu::register(
            MenuItem::make('Test', '/test-route')
        );

        $this->assertEquals('/test-route', Menu::get()->first()->route);
    }

    public function test_user_cannot_see_menu_items_that_is_not_supposed_to_be_seen(): void
    {
        Menu::clear();

        $this->asRegularUser()->signIn();

        Menu::register(MenuItem::make('test-item-1', '/')
            ->canSee(function () {
                return false;
            }));

        Menu::register(MenuItem::make('test-item-2', '/')
            ->canSeeWhen('dummy-ability'));

        Menu::register(MenuItem::make('test-item-3', '/'));

        $this->assertCount(1, Menu::get());
    }
}
