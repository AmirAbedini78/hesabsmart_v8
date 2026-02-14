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

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\CustomFieldCollection;
use Modules\Core\Fields\CustomFieldFactory;
use Modules\Core\Fields\CustomFieldFileCache;
use Modules\Core\Fields\Field;
use Modules\Core\Resource\Resource;

class CustomField extends Model
{
    /**
     * @var \Modules\Core\Fields\Field
     */
    protected $instance;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'field_type', 'field_id', 'resource_name', 'label', 'is_unique',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_unique' => 'boolean',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted()
    {
        static::saved(function () {
            CustomFieldFileCache::refresh();
        });

        static::deleted(function () {
            CustomFieldFileCache::refresh();
        });
    }

    /**
     * A custom field has many options
     */
    public function options(): HasMany
    {
        return $this->hasMany(CustomFieldOption::class);
    }

    /**
     * Get the optionable custom field model relation name
     *
     * https://laravel.com/docs/7.x/eloquent-relationships#defining-relationships
     * "Relationship names cannot collide with attribute names as that could lead to your model not being able to know which one to resolve."
     */
    protected function relationName(): Attribute
    {
        return Attribute::get(
            fn () => 'customField'.Str::studly($this->field_id)
        );
    }

    /**
     * Get the instance from the field class
     */
    public function instance(): Field
    {
        if (! $this->instance) {
            $this->instance = CustomFieldFactory::createInstance($this);
        }

        return $this->instance;
    }

    /**
     * Get the field resource instance.
     */
    public function resource(): Resource
    {
        return Innoclapps::resourceByName($this->resource_name);
    }

    /**
     * Check whether the custom field is multi optionable
     */
    public function isMultiOptionable(): bool
    {
        return $this->instance()->isMultiOptionable();
    }

    /**
     * Check whether the custom field is optionable
     */
    public function isOptionable(): bool
    {
        return $this->instance()->isOptionable();
    }

    /**
     * Get the database index name when the field is unique.
     */
    public function uniqueIndexName(): string
    {
        return $this->field_id.'_unique_index';
    }

    /**
     * Prepate the selected options for front-end
     *
     * @param  \Illuminate\Database\Eloquent\Model  $related
     */
    public function prepareRelatedOptions($related): array
    {
        return $this->prepareOptions($related->{$this->relationName});
    }

    /**
     * Check whether the custom field is unique.
     */
    public function isUnique(): bool
    {
        return $this->is_unique === true;
    }

    /**
     * Label attribute accessor
     *
     * Supports translation from language file
     */
    protected function label(): Attribute
    {
        return Attribute::get(function (?string $value, array $attributes) {
            $customKey = 'custom.custom_field.'.$attributes['field_id'];

            if (Lang::has($customKey)) {
                return __($customKey);
            } elseif (Lang::has($value)) {
                return __($value);
            }

            return $value;
        });
    }

    /**
     * Prepare the options for front-end
     */
    public function prepareOptions(?Collection $options = null): array
    {
        return ($options ?? $this->options)->map(
            fn (CustomFieldOption $option) => [
                'id' => $option->id,
                'name' => $option->name,
                'swatch_color' => $option->swatch_color,
            ]
        )->all();
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new CustomFieldCollection($models);
    }
}
