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

namespace Modules\Deals\Tests\Feature;

use Illuminate\Support\Facades\Lang;
use Modules\Deals\Models\LostReason;
use Tests\TestCase;

class LostReasonModelTest extends TestCase
{
    public function test_lost_reason_can_be_translated_with_custom_group(): void
    {
        $model = LostReason::create(['name' => 'Original']);

        Lang::addLines(['custom.lost_reason.'.$model->id => 'Changed'], 'en');

        $this->assertSame('Changed', $model->name);
    }

    public function test_lost_reason_can_be_translated_with_lang_key(): void
    {
        $model = LostReason::create(['name' => 'custom.lost_reason.some']);

        Lang::addLines(['custom.lost_reason.some' => 'Changed'], 'en');

        $this->assertSame('Changed', $model->name);
    }

    public function test_it_uses_database_name_when_no_custom_trans_available(): void
    {
        $model = LostReason::create(['name' => 'Database Name']);

        $this->assertSame('Database Name', $model->name);
    }
}
