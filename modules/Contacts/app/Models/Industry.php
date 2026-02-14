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

namespace Modules\Contacts\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Modules\Contacts\Database\Factories\IndustryFactory;
use Modules\Core\Contracts\Resources\Resourceable as ResourceableContract;
use Modules\Core\Models\CacheModel;
use Modules\Core\Resource\Resourceable;

class Industry extends CacheModel implements ResourceableContract
{
    use HasFactory,
        Resourceable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::deleting(function (Industry $model) {
            if ($model->companies()->count() > 0) {
                abort(Response::HTTP_CONFLICT, __(
                    'core::resource.associated_delete_warning',
                    [
                        'resource' => __('contacts::company.industry.industry'),
                    ]
                ));
            }
            $model->companies()->onlyTrashed()->update(['industry_id' => null]);
        });
    }

    /**
     * An industry has many companies
     */
    public function companies(): HasMany
    {
        return $this->hasMany(\Modules\Contacts\Models\Company::class);
    }

    /**
     * Name attribute accessor
     *
     * Supports translation from language file
     */
    protected function name(): Attribute
    {
        return Attribute::get(function (?string $value, array $attributes) {
            if (! array_key_exists('id', $attributes)) {
                return $value;
            }

            $customKey = 'custom.industry.'.$attributes['id'];

            if (Lang::has($customKey)) {
                return __($customKey);
            } elseif (Lang::has($value)) {
                return __($value);
            }

            return $value;
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): IndustryFactory
    {
        return IndustryFactory::new();
    }
}
