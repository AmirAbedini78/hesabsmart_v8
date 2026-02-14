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

namespace Modules\Core\Tests\Feature\Controller\Api\Resource;

use Tests\TestCase;

class FilterRulesControllerTest extends TestCase
{
    public function test_user_can_retrieve_resource_rules(): void
    {
        $this->signIn();

        $this->getJson('/api/contacts/rules')->assertJsonFragment(['id' => 'first_name']);
    }
}
