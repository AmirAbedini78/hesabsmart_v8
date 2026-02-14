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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\Field;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Fields\Numeric;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Exceptions\InvalidExportTypeException;
use Modules\Users\Models\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Export implements FromQuery, WithHeadings, WithMapping
{
    /**
     * The allowed export file types
     *
     * @var array
     */
    const ALLOWED_TYPES = [
        'csv' => \Maatwebsite\Excel\Excel::CSV,
        'xls' => \Maatwebsite\Excel\Excel::XLS,
        'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
    ];

    /**
     * Default export type
     *
     * @var string
     */
    const DEFAULT_TYPE = 'csv';

    /**
     * The fields that are available for export.
     */
    public FieldsCollection $availableFields;

    /**
     * The user performing the export.
     */
    public ?User $user = null;

    /**
     * Create new Export instance.
     */
    public function __construct(protected Resource $resource, protected Builder $query)
    {
        $this->availableFields = $resource->fieldsForExport();
    }

    /**
     * Map the export rows
     *
     * @param  \Modules\Core\Models\Model  $model
     */
    public function map($model): array
    {
        return $this->getFields()->map(
            fn (Field $field) => $field->resolveForExport($model)
        )->all();
    }

    /**
     * Provides the export eadings.
     */
    public function headings(): array
    {
        return $this->getFields()->map(
            fn (Field $field) => $this->heading($field)
        )->all();
    }

    /**
     * Create heading for export for the given field.
     */
    public function heading(Field $field): string
    {
        $label = $field->label;

        if ($field instanceof DateTime) {
            if ($this->user && isset($field->meta()['export_local'])) {
                $label .= ' ('.$this->user->timezone.')';
            } else {
                $label .= ' ('.config('app.timezone').')';
            }
        }

        if ($field instanceof Numeric && isset($field->meta()['export_raw'])) {
            $label .= ' (Raw Value)';
        }

        return $label;
    }

    /**
     * Set the user performing the export.
     */
    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set fields to be used when exporting.
     */
    public function setFields(FieldsCollection $fields): static
    {
        $this->availableFields = $fields;

        return $this;
    }

    /**
     * Get the fields for the export.
     */
    public function getFields(): FieldsCollection
    {
        return $this->fieldsWithAdditionalColumns();
    }

    /**
     * Get fields collection with additional columns for DateTime and Numeric fields.
     */
    public function fieldsWithAdditionalColumns(): FieldsCollection
    {
        // After each DateTime field add a copy of the same field which will display
        // the date in user timezone (when user is set).
        // Also, for Numeric fields with currency, add a raw value copy.
        $newFields = $this->availableFields->empty();

        foreach ($this->availableFields as $field) {
            $newFields->push($field);

            if ($field instanceof DateTime && $this->user) {
                $newFields->push($this->asLocalDateTimeField($field));
            }

            if ($field instanceof Numeric && $field->currency) {
                $newFields->push($this->asRawNumericField($field));
            }
        }

        return $newFields;
    }

    /**
     * Make a copy of the given date time field to local.
     */
    public function asLocalDateTimeField(DateTime $from): DateTime
    {
        $newField = clone $from;

        $newField->withMeta(['export_local' => true])
            ->exportUsing(function (Model $model, $value) {
                if ($value instanceof Carbon) {
                    return $value->tz($this->user->timezone);
                } elseif (is_string($value)) {
                    return Carbon::parse($value)->tz($this->user->timezone);
                }

                return $value;
            });

        return $newField;
    }

    /**
     * Make a copy of the given numeric field with raw value.
     */
    public function asRawNumericField(Numeric $from): Numeric
    {
        $newField = clone $from;

        $newField->withMeta(['export_raw' => true])
            ->exportUsing(function (Model $model, $value) {
                // Return the raw numeric value without currency formatting
                if (is_null($value)) {
                    return 0;
                }

                return (float) $value;
            });

        return $newField;
    }

    /**
     * Perform and download the export
     */
    public function download(?string $type = null): BinaryFileResponse
    {
        return Excel::download(
            $this,
            $this->fileName().'.'.$type,
            $this->determineType($type)
        );
    }

    /**
     * Provide the export query.
     *
     * {@inheritdoc}
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * The export file name (without extension)
     */
    public function fileName(): string
    {
        return $this->resource->name();
    }

    /**
     * Determine the type.
     *
     * @throws \Modules\Core\Resource\Exceptions\InvalidExportTypeException
     */
    protected function determineType(?string $type): string
    {
        if (is_null($type)) {
            return static::ALLOWED_TYPES[static::DEFAULT_TYPE];
        } elseif (! array_key_exists($type, static::ALLOWED_TYPES)) {
            throw new InvalidExportTypeException($type);
        }

        return static::ALLOWED_TYPES[$type];
    }
}
