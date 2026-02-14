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

namespace Modules\Core\Tests\Feature\Filters;

use Illuminate\Support\Facades\Request;
use Modules\Core\Filters\Checkbox;
use Modules\Core\Filters\Exceptions\QueryBuilderException;
use Modules\Core\Filters\Number;
use Modules\Core\Table\TableSettings;
use Modules\Core\Tests\Concerns\TestsFilters;
use Tests\Fixtures\EventTable;
use Tests\TestCase;

class FiltersTest extends TestCase
{
    use TestsFilters;

    protected static $filter;

    public function test_user_cannot_see_filters_that_is_not_authorized_to_see(): void
    {
        $user = $this->signIn();
        Request::setUserResolver(fn () => $user);

        $table = new EventTable;
        $settings = new TableSettings($table, $user);

        $this->assertCount(1, $settings->toArray()['rules']);
    }

    public function test_throw_an_exception_when_rule_between_operator_value_is_not_array(): void
    {
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('The "between" needs an array value.');

        static::$filter = Number::class;

        $this->perform('dummy-attribute', 'between', 'string-value');
    }

    public function test_throw_an_exception_when_rule_not_between_operator_value_is_not_array(): void
    {
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('The "not_between" needs an array value.');

        static::$filter = Number::class;

        $this->perform('dummy-attribute', 'not_between', 'string-value');
    }

    public function test_throw_an_exception_when_rule_in_operator_value_is_not_array(): void
    {
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('The "in" needs an array value.');

        static::$filter = Checkbox::class;

        $this->perform('dummy-attribute', 'in', 'string-value');
    }

    public function test_throw_an_exception_when_rule_not_in_operator_value_is_not_array(): void
    {
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('The "not_in" needs an array value.');

        static::$filter = Checkbox::class;

        $this->perform('dummy-attribute', 'not_in', 'string-value');
    }
}
