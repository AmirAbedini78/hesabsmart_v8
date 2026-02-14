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

namespace Modules\Notes\Resources;

use Modules\Comments\Contracts\HasComments;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Criteria\ViaRelatedResourcesCriteria;
use Modules\Core\Fields\Editor;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Resource\Resource;
use Modules\Notes\Http\Resources\NoteResource;

class Note extends Resource implements HasComments, WithResourceRoutes
{
    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Notes\Models\Note';

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return NoteResource::class;
    }

    /**
     * Provide the available resource fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            Editor::make('body')->rules(['required', 'string'])->withMentions()->onlyOnForms(),
        ];
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): ?string
    {
        if (! auth()->user()->isSuperAdmin()) {
            return ViaRelatedResourcesCriteria::class;
        }

        return null;
    }

    /**
     * Get the resource relationship name when it's associated
     */
    public function associateableName(): string
    {
        return 'notes';
    }

    /**
     * Get the resource rules available for create and update
     */
    public function rules(ResourceRequest $request): array
    {
        return [
            'via_resource' => ['required', 'in:contacts,companies,deals', 'string'],
            'via_resource_id' => ['required', 'numeric'],
        ];
    }

    /**
     * Get the custom validation messages for the resource
     */
    public function validationMessages(): array
    {
        return [
            'body.required' => __('validation.required_without_label'),
        ];
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return __('notes::note.notes');
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return __('notes::note.note');
    }
}
