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

namespace Modules\Core\Common\Changelog;

use Closure;
use Modules\Core\Contracts\Fields\Customfieldable;
use Modules\Core\Fields\CustomFieldCollection;
use Modules\Core\Fields\Field;
use Spatie\Activitylog\Contracts\LoggablePipe;
use Spatie\Activitylog\EventLogBag;

class CustomFieldsChangesLogger implements LoggablePipe
{
    /**
     * The pre sync option names.
     */
    protected static array $preSyncOptions = [];

    /**
     * Initialize new CustomFieldsChangesLogger instance.
     */
    public function __construct(string $model)
    {
        $this->trackMultiOptionableCustomFieldChanges($model);
    }

    /**
     * Handle the piped item for changelog.
     */
    public function handle(EventLogBag $event, Closure $next): EventLogBag
    {
        if (method_exists($event->model, 'getCustomFields')) {
            $customFields = $event->model->getCustomFields();

            $event->changes = collect($event->changes)->map(
                fn (array $props) => $this->formatRegularAndSingleOptionsFieldsProps($props, $customFields)
            )->all();
        }

        return $next($event);
    }

    /**
     * Register event listener to track the multie optionable custom fields changes.
     */
    protected function trackMultiOptionableCustomFieldChanges(string $model): void
    {
        $model::syncingCustomFieldOptions($this->beforeSyncCustomFieldOptions(...));
        $model::syncedCustomFieldOptions($this->afterSyncCustomFieldOptions(...));
    }

    /**
     * Get the callback for before sync custom field options.
     */
    protected function beforeSyncCustomFieldOptions($model, Field&Customfieldable $field, array $ids): void
    {
        if ($model->wasRecentlyCreated) {
            return;
        }

        static::$preSyncOptions[$model::class][$field->attribute] = $field->resolveForDisplay($model);
    }

    /**
     * Get the callback for after sync custom field options.
     */
    protected function afterSyncCustomFieldOptions($model, Field&Customfieldable $field, array $ids): void
    {
        if ($model->wasRecentlyCreated) {
            return;
        }

        $newValue = $field->resolveForDisplay(
            $model->load($field->customField->relationName)
        );

        $oldValue = static::$preSyncOptions[$model::class][$field->attribute];

        if ($newValue != $oldValue) {
            static::logMultiOptionableChangelog($newValue, $oldValue, $model, $field);
        }
    }

    /**
     * Log multioptionable custom field options changed activity.
     *
     * @param  string  $newValue
     * @param  string  $oldValue
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Modules\Core\Contracts\Fields\Customfieldable&\Modules\Core\Fields\Field  $field
     * @return void
     */
    protected static function logMultiOptionableChangelog($newValue, $oldValue, $model, $field)
    {
        $model->logDirtyAttributesOnLatestLog([
            'attributes' => [
                $field->attribute => [
                    'label' => $field->label,
                    'value' => $newValue,
                ],
            ],
            'old' => [
                $field->attribute => [
                    'label' => $field->label,
                    'value' => $oldValue,
                ],
            ],
        ], $model);
    }

    /**
     * Ensures custom fields properties are properly logged
     * Used for single optionable and regular custom field.
     */
    protected function formatRegularAndSingleOptionsFieldsProps(array $properties, CustomFieldCollection $customFields): array
    {
        $ids = $customFields->pluck('field_id')->all();

        foreach (array_keys($properties) as $property) {
            if (! in_array($property, $ids)) {
                continue;
            }

            if (! $customField = $customFields->firstWhere('field_id', $property)) {
                continue;
            }

            // Custom fields are formatted with label and value keys
            // Because if a user delete a custom field, the label will be lost
            // and we updated activity won't be shown properly without the label
            // In this case, we keep the label in the activity itself
            $properties[$property] = [
                'label' => $customField->label,
                'value' => with($properties[$property], function ($value) use ($customField) {
                    if ($customField->isOptionable()) {
                        return $value;

                        return $customField->options->find($value)?->name ?? $value;
                    }

                    return $value;
                }),
            ];
        }

        return $properties;
    }
}
