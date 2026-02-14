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

namespace Modules\Calls\Resources;

use Modules\Calls\Http\Resources\CallOutcomeResource;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Fields\ColorSwatch;
use Modules\Core\Fields\Text;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Resource\Resource;
use Modules\Core\Rules\StringRule;

class CallOutcome extends Resource implements WithResourceRoutes
{
    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Calls\Models\CallOutcome';

    /**
     * Set the available resource fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            Text::make('name', __('calls::call.outcome.name'))->rules(['required', StringRule::make()])->unique(static::$model),
            ColorSwatch::make('swatch_color', __('core::app.colors.color')),
        ];
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return CallOutcomeResource::class;
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return __('calls::call.outcome.call_outcome');
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return __('calls::call.outcome.outcomes');
    }
}
