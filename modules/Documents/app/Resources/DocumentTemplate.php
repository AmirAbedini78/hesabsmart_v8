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

namespace Modules\Documents\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Modules\Core\Actions\CloneAction;
use Modules\Core\Actions\DeleteAction;
use Modules\Core\Contracts\Resources\Cloneable;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Fields\Boolean;
use Modules\Core\Fields\CreatedAt;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Fields\ID;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\UpdatedAt;
use Modules\Core\Fields\User as UserField;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Resource;
use Modules\Core\Rules\StringRule;
use Modules\Core\Rules\UniqueResourceRule;
use Modules\Core\Table\Column;
use Modules\Core\Table\Table;
use Modules\Documents\Criteria\TemplatesForUserCriteria;
use Modules\Documents\Enums\DocumentViewType;
use Modules\Documents\Http\Resources\DocumentTemplateResource;
use Modules\Documents\Models\DocumentTemplate as DocumentTemplateModel;

class DocumentTemplate extends Resource implements Cloneable, Tableable, WithResourceRoutes
{
    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Documents\Models\DocumentTemplate';

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): string
    {
        return TemplatesForUserCriteria::class;
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return DocumentTemplateResource::class;
    }

    /**
     * Clone the resource record from the given id
     */
    public function clone(Model $model, int $userId): Model
    {
        return $model->clone($userId);
    }

    /**
     * Provide the resource table class instance.
     */
    public function table(Builder $query, ResourceRequest $request, string $identifier): Table
    {
        return Table::make($query, $request, $identifier)->orderBy('created_at', 'desc')->withActionsColumn();
    }

    /**
     * Provides the resource available actions
     */
    public function actions(ResourceRequest $request): array
    {
        return [
            CloneAction::make()->onlyInline(),

            DeleteAction::make()->canRun(function (ActionRequest $request, Model $model) {
                return $request->user()->can('delete', $model);
            })->showInline(),
        ];
    }

    /**
     * Create new resource template in storage.
     */
    public function create(Model $model, ResourceRequest $request): Model
    {
        $model->fill($request->all())->save();

        return $model;
    }

    /**
     * Create new resource template in storage.
     */
    public function update(Model $model, ResourceRequest $request): Model
    {
        $model->fill($request->all())->save();

        return $model;
    }

    /**
     * Get the resource search columns.
     */
    public function searchableColumns(): array
    {
        return ['name' => 'like'];
    }

    /**
     * Get the fields for index.
     */
    public function fieldsForIndex(): FieldsCollection
    {
        $indexFields = new FieldsCollection([
            ID::make(),

            Text::make('name', __('documents::document.template.name'))->tapIndexColumn(
                fn (Column $column) => $column->route('/document-templates/{id}/edit')
            ),

            Boolean::make('is_shared', __('documents::document.template.is_shared')),

            UserField::make(__('core::app.created_by')),

            CreatedAt::make(),

            UpdatedAt::make(),
        ]);

        return $this->resolveFields()
            ->push(...$indexFields)
            ->disableInlineEdit()
            ->filterForIndex();
    }

    /**
     * Set the resource rules available for create and update
     */
    public function rules(ResourceRequest $request): array
    {
        return [
            'name' => [
                'required',
                StringRule::make(),
                UniqueResourceRule::make(DocumentTemplateModel::class),
            ],
            'content' => ['required', 'string'],
            'is_shared' => ['nullable', 'boolean'],
            'view_type' => ['nullable', Rule::enum(DocumentViewType::class)],
        ];
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return __('documents::document.template.template');
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return __('documents::document.template.templates');
    }
}
