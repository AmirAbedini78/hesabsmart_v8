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

use Modules\Core\Http\Requests\ResourceRequest;
use Tests\Fixtures\EventTable;
use Tests\TestCase;

class TableTest extends TestCase
{
    public function test_non_existing_columns_are_removed_from_table_order(): void
    {
        $user = $this->signIn();

        $request = app(ResourceRequest::class)->setUserResolver(fn () => $user);

        $table = (new EventTable(null, $request))->orderBy('non-existent-field', 'desc');

        $this->assertEmpty($table->settings()->getDefaultSettings()['order']);
    }
}
