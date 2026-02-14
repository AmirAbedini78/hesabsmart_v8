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

namespace Modules\Core\Fields;

use Illuminate\Support\Str;
use Modules\Core\Table\RelationshipCountColumn;

class RelationshipCount extends Field
{
    /**
     * The relationship being counted.
     */
    public string $countRelation;

    /**
     * Indicates if the field is searchable.
     */
    protected bool $searchable = false;

    /**
     * Create new instance of RelationshipCount field.
     *
     * @param  string  $attribute
     * @param  string  $label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct(Str::snake($attribute).'_count', $label);

        $this->countRelation = $attribute;

        $this->onlyOnIndex()
            ->excludeFromSettings()
            ->excludeFromImport()
            ->fillUsing(function () {});
    }

    /**
     * Provide the column used for index.
     */
    public function indexColumn(): RelationshipCountColumn
    {
        return new RelationshipCountColumn($this->countRelation, $this->label);
    }

    /**
     * Resolve the field value for export.
     */
    public function resolveForExport($model)
    {
        return (string) (parent::resolveForExport($model) ?: 0);
    }

    /**
     * Get the mailable template placeholder.
     *
     * For count field, the placeholders is disabled, it works fine, but causes too many placeholders by default.
     *
     * @param  \Modules\Core\Models\Model|null  $model
     */
    public function mailableTemplatePlaceholder($model)
    {
        return null;
    }

    /**
     * Resolve the field value for JSON Resource.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array|null
     */
    public function resolveForJsonResource($model)
    {
        $value = $model->{$this->attribute};

        // We will check if the counted relation not set (null),
        // in the model attributes, this means that the relation is not loaded,
        // we will just return null to prevent the attribute to be added in the response.
        return ! is_null($value) ? [$this->attribute => (int) $value] : null;
    }
}
