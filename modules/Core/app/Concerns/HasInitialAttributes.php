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

namespace Modules\Core\Concerns;

use Illuminate\Support\Arr;
use Modules\Core\Models\Model;

/** @mixin \Modules\Core\Models\Model */
trait HasInitialAttributes
{
    protected static array $extraInitialAttributes = [];

    /**
     * Boot HasInitialAttributes trait.
     */
    protected static function bootHasInitialAttributes(): void
    {
        static::creating(function (Model $model) {
            $model->mergeInitialAttributes();
        });
    }

    /**
     * Get the model initial attributes with dot notation.
     */
    abstract public static function getInitialAttributes(): array;

    /**
     * Add extra initial attributes to the model.
     */
    public static function withInitialAttributes(array $attributes): void
    {
        static::$extraInitialAttributes = array_merge(static::$extraInitialAttributes, $attributes);
    }

    /**
     * Merge the model initial attributes.
     */
    public function mergeInitialAttributes(): static
    {
        $defaults = array_merge(static::$extraInitialAttributes, static::getInitialAttributes());

        // Map the attributes with their actual value (casted)
        $attributes = collect($this->getAttributes())->map(
            fn ($value, $key) => $this->getAttribute($key)
        )->all();

        foreach ($defaults as $path => $value) {
            if (! Arr::has($attributes, $path) || blank(Arr::get($attributes, $path))) {
                Arr::set($attributes, $path, $value);
            }
        }

        $this->forceFill($attributes);

        return $this;
    }
}
