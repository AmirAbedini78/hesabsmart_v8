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

namespace Modules\Billable\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Billable\Database\Factories\ProductFactory;
use Modules\Billable\Observers\ProductObserver;
use Modules\Core\Concerns\HasCreator;
use Modules\Core\Concerns\LazyTouchesViaPivot;
use Modules\Core\Concerns\Prunable;
use Modules\Core\Contracts\Resources\Resourceable as ResourceableContract;
use Modules\Core\Models\CacheModel;
use Modules\Core\Resource\Resourceable;

#[ObservedBy(ProductObserver::class)]
class Product extends CacheModel implements ResourceableContract
{
    use HasCreator,
        HasFactory,
        LazyTouchesViaPivot,
        Prunable,
        Resourceable,
        SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'unit_price' => 'decimal:3',
        'direct_cost' => 'decimal:3',
        'tax_rate' => 'decimal:3',
        'created_by' => 'int',
    ];

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Get the product billable products.
     */
    public function billables(): HasMany
    {
        return $this->hasMany(\Modules\Billable\Models\BillableProduct::class, 'product_id');
    }

    /**
     * Clone the product.
     */
    public function clone(int $userId): Product
    {
        $newModel = $this->replicate(['sku']);
        $newModel->created_by = $userId;
        $newModel->name = clone_prefix($newModel->name);

        $newModel->save();

        return $newModel;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
