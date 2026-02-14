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

namespace Modules\Billable\Providers;

use Modules\Billable\Enums\TaxType;
use Modules\Billable\Listeners\TransferProductsUserData;
use Modules\Billable\Models\Billable;
use Modules\Billable\Models\BillableProduct;
use Modules\Core\Settings\DefaultSettings;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Users\Events\TransferringUserData;

class BillableServiceProvider extends ModuleServiceProvider
{
    protected bool $withViews = true;

    protected array $resources = [
        \Modules\Billable\Resources\Product::class,
    ];

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(TransferringUserData::class, TransferProductsUserData::class);
    }

    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Configure the module.
     */
    protected function setup(): void
    {
        DefaultSettings::addRequired('tax_label', 'TAX');
        DefaultSettings::add('tax_rate', 0);
        DefaultSettings::addRequired('tax_type', 'no_tax');
        DefaultSettings::addRequired('discount_type', 'percent');
    }

    /**
     * Provide the data to share on the front-end.
     */
    protected function scriptData(): array
    {
        return [
            'tax_type' => Billable::defaultTaxType()?->name,
            'tax_label' => BillableProduct::defaultTaxLabel(),
            'tax_rate' => BillableProduct::defaultTaxRate(),
            'discount_type' => BillableProduct::defaultDiscountType(),
            'taxes' => [
                'types' => TaxType::names(),
            ],
        ];
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Billable';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'billable';
    }
}
