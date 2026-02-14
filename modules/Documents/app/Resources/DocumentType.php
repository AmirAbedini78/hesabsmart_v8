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

use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Criteria\VisibleModelsCriteria;
use Modules\Core\Fields\ColorSwatch;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\VisibilityGroup;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Resource\Resource;
use Modules\Core\Rules\StringRule;
use Modules\Documents\Http\Resources\DocumentTypeResource;

class DocumentType extends Resource implements WithResourceRoutes
{
    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Documents\Models\DocumentType';

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): string
    {
        return VisibleModelsCriteria::class;
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return DocumentTypeResource::class;
    }

    /**
     * Provide the available resource fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            Text::make('name', __('documents::document.type.name'))
                ->rules(['required', StringRule::make()])
                ->unique(static::$model)
                ->showValueWhenUnauthorizedToView(),
            ColorSwatch::make('swatch_color', __('core::app.colors.color'))
                ->showValueWhenUnauthorizedToView(),
            VisibilityGroup::make('visibility_group'),
        ];
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return __('documents::document.type.type');
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return __('documents::document.type.types');
    }
}
