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

namespace Modules\Calls\Tests\Feature;

use Illuminate\Support\Facades\Lang;
use Modules\Calls\Models\Call;
use Modules\Calls\Models\CallOutcome;
use Tests\TestCase;

class CallOutcomeModelTest extends TestCase
{
    public function test_outcome_has_calls(): void
    {
        $outcome = CallOutcome::factory()->has(Call::factory()->count(2))->create();

        $this->assertCount(2, $outcome->calls);
    }

    public function test_call_outcome_can_be_translated_with_custom_group(): void
    {
        $model = CallOutcome::factory()->create(['name' => 'Original']);

        Lang::addLines(['custom.call_outcome.'.$model->id => 'Changed'], 'en');

        $this->assertSame('Changed', $model->name);
    }

    public function test_call_outcome_can_be_translated_with_lang_key(): void
    {
        $model = CallOutcome::factory()->create(['name' => 'custom.call_outcome.some']);

        Lang::addLines(['custom.call_outcome.some' => 'Changed'], 'en');

        $this->assertSame('Changed', $model->name);
    }

    public function test_it_uses_database_name_when_no_custom_trans_available(): void
    {
        $model = CallOutcome::factory()->create(['name' => 'Database Name']);

        $this->assertSame('Database Name', $model->name);
    }
}
