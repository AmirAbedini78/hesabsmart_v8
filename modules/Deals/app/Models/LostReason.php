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

namespace Modules\Deals\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Lang;
use Modules\Core\Contracts\Resources\Resourceable as ResourceableContract;
use Modules\Core\Models\CacheModel;
use Modules\Core\Resource\Resourceable;
use Modules\Deals\Database\Factories\LostReasonFactory;

class LostReason extends CacheModel implements ResourceableContract
{
    use HasFactory, Resourceable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

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

            $customKey = 'custom.lost_reason.'.$attributes['id'];

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
    protected static function newFactory(): LostReasonFactory
    {
        return LostReasonFactory::new();
    }
}
