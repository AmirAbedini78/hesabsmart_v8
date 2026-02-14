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

namespace Modules\Activities\Fields;

use Modules\Activities\Http\Resources\ActivityTypeResource;
use Modules\Activities\Models\ActivityType as ActivityTypeModel;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\BelongsTo;
use Modules\Core\Table\Column;

class ActivityType extends BelongsTo
{
    /**
     * Field component.
     */
    protected static $component = 'activity-type-field';

    /**
     * Create new instance of ActivityType field.
     */
    public function __construct()
    {
        parent::__construct('type', ActivityTypeModel::class, __('activities::activity.type.type'));

        $this
            ->withDefaultValue(function () {
                if (is_null($type = ActivityTypeModel::getDefaultType())) {
                    return null;
                }

                return ActivityTypeModel::select('id')->find($type)?->getKey();
            })
            ->inlineEditWith(
                BelongsTo::make('type', ActivityTypeModel::class, __('activities::activity.type.type'))
                    ->valueKey('id')
                    ->labelKey('name')
                    ->rules('required')
                    ->withoutClearAction()
                    ->options(
                        Innoclapps::resourceByModel(ActivityTypeModel::class)
                    )
            )
            ->setJsonResource(ActivityTypeResource::class)
            ->tapIndexColumn(function (Column $column) {
                $column->select($cols = ['icon', 'swatch_color'])->appends($cols)->width('200px');
            })
            ->options(Innoclapps::resourceByModel(ActivityTypeModel::class))
            ->acceptLabelAsValue(false);
    }
}
