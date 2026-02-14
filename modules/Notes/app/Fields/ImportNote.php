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

namespace Modules\Notes\Fields;

use Exception;
use Modules\Core\Fields\Field;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Table\Column;
use Modules\Notes\Models\Note;

class ImportNote extends Field
{
    /**
     * Indicates if the field is searchable.
     */
    protected bool $searchable = false;

    /**
     * Initialize new ImportNote instance class.
     *
     * @param  string  $attribute  field attribute
     * @param  string|null  $label  field label
     */
    public function __construct($attribute = 'import_note', $label = null)
    {
        parent::__construct($attribute, $label ?: __('notes::note.note'));

        $this->onlyForImport()
            ->fillUsing(function ($model, $attribute, ResourceRequest $request, $value, $requestAttribute) {
                if (empty($value)) {
                    return;
                }

                return function () use ($model, $request, $value) {
                    if (! $model->notes()->where('body', $value)->exists()) {
                        $note = Note::create(['body' => $value]);

                        $note->{$request->resource()->associateableName()}()->attach($model);
                    }
                };
            });
    }

    /**
     * Get the mailable template placeholder.
     *
     * @param  \Modules\Core\Models\Model|null  $model
     */
    public function mailableTemplatePlaceholder($model)
    {
        return null;
    }

    /**
     * Provide the column used for index.
     *
     * @return null
     */
    public function indexColumn(): ?Column
    {
        return null;
    }

    /**
     * Resolve the actual field value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function resolve($model)
    {
        return null;
    }

    /**
     * Resolve the displayable field value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function resolveForDisplay($model)
    {
        return null;
    }

    /**
     * Resolve the field value for export.
     *
     * @param  \Modules\Core\Models\Model  $model
     */
    public function resolveForExport($model)
    {
        return null;
    }

    /**
     * Resolve the field value for JSON Resource.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function resolveForJsonResource($model)
    {
        return null;
    }

    /**
     * Add custom value resolver.
     */
    public function resolveUsing(callable $resolveCallback): never
    {
        throw new Exception(__CLASS__.' cannot have custom resolve callback');
    }

    /**
     * Add custom display resolver.
     */
    public function displayUsing(callable $displayCallback): never
    {
        throw new Exception(__CLASS__.' cannot have custom display callback');
    }

    /**
     * Add custom JSON resource callback.
     */
    public function resolveForJsonResourceUsing(callable $callback): static
    {
        throw new Exception(__CLASS__.' cannot have custom JSON resource callback');
    }
}
