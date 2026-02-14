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

namespace Modules\Contacts\Tests\Feature;

use Illuminate\Support\Facades\Lang;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Industry;
use Tests\TestCase;

class IndustryModelTest extends TestCase
{
    public function test_industry_has_companies(): void
    {
        $industry = Industry::factory()->has(Company::factory()->count(2))->create();

        $this->assertCount(2, $industry->companies);
    }

    public function test_industry_with_companies_cannot_be_deleted(): void
    {
        $industry = Industry::factory()->has(Company::factory())->create();

        $this->expectExceptionMessage(__(
            'core::resource.associated_delete_warning',
            [
                'resource' => __('contacts::company.industry.industry'),
            ]
        ));

        $industry->delete();
    }

    public function test_industry_can_be_translated_with_custom_group(): void
    {
        $model = Industry::factory()->create(['name' => 'Original']);

        Lang::addLines(['custom.industry.'.$model->id => 'Changed'], 'en');

        $this->assertSame('Changed', $model->name);
    }

    public function test_industry_can_be_translated_with_lang_key(): void
    {
        $model = Industry::factory()->create(['name' => 'custom.industry.some']);

        Lang::addLines(['custom.industry.some' => 'Changed'], 'en');

        $this->assertSame('Changed', $model->name);
    }

    public function test_it_uses_database_name_when_no_custom_trans_available(): void
    {
        $model = Industry::factory()->create(['name' => 'Database Name']);

        $this->assertSame('Database Name', $model->name);
    }
}
