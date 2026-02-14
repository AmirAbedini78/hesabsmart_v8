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

namespace Modules\Deals\Resources;

use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Fields\Textarea;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Resource\Resource;
use Modules\Core\Rules\StringRule;
use Modules\Deals\Http\Resources\LostReasonResource;

class LostReason extends Resource implements WithResourceRoutes
{
    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Deals\Models\LostReason';

    /**
     * Set the available resource fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            Textarea::make('name', __('deals::deal.lost_reasons.name'))->rules(['required', StringRule::make()])->unique(static::$model),
        ];
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return LostReasonResource::class;
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return __('deals::deal.lost_reasons.lost_reason');
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return __('deals::deal.lost_reasons.lost_reasons');
    }
}
