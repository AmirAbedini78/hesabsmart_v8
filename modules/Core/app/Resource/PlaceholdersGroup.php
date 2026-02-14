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

namespace Modules\Core\Resource;

use Modules\Core\Common\Placeholders\GenericPlaceholder;
use Modules\Core\Common\Placeholders\Placeholder;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\Field;
use Modules\Core\Models\Model;

class PlaceholdersGroup
{
    protected Resource $resource;

    /**
     * Initialize new PlaceholdersGroup instance.
     *
     * @param  \Modules\Core\Models\Model|null  $model  Provide the model when parsing is needed
     */
    public function __construct(Resource|string $resource, protected ?Model $model = null)
    {
        $this->resource = is_string($resource) ? Innoclapps::resourceByName($resource) : $resource;
    }

    /**
     * Get the all of the resource placeholders.
     */
    public function all(): array
    {
        return $this->resource->fieldsForPlaceholders()
            ->map(function (Field $field) {
                return $this->getPlaceholderFromField($field);
            })
            ->each(function (Placeholder $placeholder) {
                $placeholder->prefixTag($this->tagPrefix());
            })
            ->values()
            ->all();
    }

    /**
     * Get placeholders from the given field.
     */
    protected function getPlaceholderFromField(Field $field)
    {
        $placeholder = $field->mailableTemplatePlaceholder($this->model);

        if (is_string($placeholder)) { // Allow pass value directly without providing placeholder
            return $this->createGenericPlaceholderFromField($field, $placeholder);
        }

        return $placeholder;
    }

    /**
     * Get the fields intended for placeholders.
     */
    protected function getFields()
    {
        return $this->resource->resolveFields();
    }

    /**
     * Create generic placeholder from field.
     */
    protected function createGenericPlaceholderFromField(Field $field, string $value)
    {
        return GenericPlaceholder::make($field->attribute)
            ->description($field->label)
            ->value($value);
    }

    /**
     * Get the model for the placeholders.
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * Get the group resource instance.
     */
    public function getResource(): Resource
    {
        return $this->resource;
    }

    /**
     * Get the placeholders tag prefix.
     */
    public function tagPrefix(): string
    {
        return $this->resource->singularName().'.';
    }
}
