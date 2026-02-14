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

use Illuminate\Support\Collection;
use Modules\Core\Http\Requests\ResourceRequest;

class FieldsCollection extends Collection
{
    /**
     * Filter only the authorized fields.
     */
    public function authorized(): static
    {
        return $this->filter->authorizedToSee()->values();
    }

    /**
     * Find field by the given attribute.
     */
    public function find(string $attribute): ?Field
    {
        return $this->firstWhere('attribute', $attribute);
    }

    /**
     * Filter only primary fields.
     */
    public function primary(): static
    {
        return $this->filter(fn (Field $field) => $field->isPrimary());
    }

    /**
     * Find field by the given request attribute.
     */
    public function findByRequestAttribute(string $attribute): ?Field
    {
        return $this->first(fn (Field $field) => $field->requestAttribute() === $attribute);
    }

    /**
     * Filter only custom fields.
     */
    public function filterCustomFields(): static
    {
        return $this->filter->isCustomField();
    }

    /**
     * Transform the fields in the collection to data array.
     */
    public function toData(ResourceRequest $request): array
    {
        return $this->mapWithKeys(function (Field $field) use ($request) {
            return [$field->attribute => $field->attributeFromRequest($request, $field->requestAttribute())];
        })->filter()->all();
    }

    /**
     * Filter the fields for creation.
     */
    public function filterForCreation(): static
    {
        return $this->filter(fn (Field $field) => $field->isApplicableForCreation())->values();
    }

    /**
     * Filter fields only visible on create view.
     */
    public function visibleOnCreate(): static
    {
        return $this->reject(fn (Field $field) => $field->showOnCreation === false)->values();
    }

    /**
     * Filter the fields for update.
     */
    public function filterForUpdate(): static
    {
        return $this->filter(fn (Field $field) => $field->isApplicableForUpdate())->values();
    }

    /**
     * Filter fields only visible on update view.
     */
    public function visibleOnUpdate(): static
    {
        return $this->reject(fn (Field $field) => $field->showOnUpdate === false)->values();
    }

    /**
     * Filter the fields for detail.
     */
    public function filterForDetail(): static
    {
        return $this->filter(fn (Field $field) => $field->isApplicableForDetail())->values();
    }

    /**
     * Filter fields only visible on detail view.
     */
    public function visibleOnDetail(): static
    {
        return $this->reject(fn (Field $field) => $field->showOnDetail === false)->values();
    }

    /**
     * Filter the fields for index.
     */
    public function filterForIndex(): static
    {
        return $this->filter(fn (Field $field) => $field->isApplicableForIndex())->values();
    }

    /**
     * Filter the fields for import sample.
     */
    public function filterForImportSample(): static
    {
        return $this->filterForImport()->reject(fn (Field $field) => $field->excludeFromImportSample);
    }

    /**
     * Filter the fields for placeholders.
     */
    public function filterForPlaceholders(): static
    {
        return $this->reject(
            fn (Field $field) => $field->excludeFromPlaceholders === true || is_null($field->mailableTemplatePlaceholder(null))
        )->values();
    }

    /**
     * Filter the fields for import.
     */
    public function filterForImport(): static
    {
        $createdAt = $this->whereInstanceOf(CreatedAt::class)->first();

        return $this
            ->reject(function (Field $field) {
                return $field->excludeFromImport || $field instanceof ID || $field instanceof UpdatedAt;
            })
            ->withoutReadonly()
            ->when(! is_null($createdAt), function (FieldsCollection $fields) use ($createdAt) {
                // First, we will check if the "CreatedAt" field is specifically excluded from the developer.
                if ($createdAt->excludeFromImport) {
                    return $fields;
                }

                // When "$fields" does not contains the "CreatedAt" field e.q. because is marked as readonly, we will add it.
                return $fields->when(
                    $fields->whereInstanceOf(CreatedAt::class)->isEmpty(),
                    fn (FieldsCollection $fields) => $fields->push($createdAt)
                );
            })
            ->values();
    }

    /**
     * Filter the fields for export.
     */
    public function filterForExport(): static
    {
        return $this->reject(fn (Field $field) => $field->excludeFromExport)->values();
    }

    /**
     * Disable inline edit for all the fields in the collection.
     */
    public function disableInlineEdit(): static
    {
        $this->each(fn (Field $field) => $field->disableInlineEdit());

        return $this;
    }

    /**
     * Apply a filter to remove fields excluded from Zapier.
     */
    public function withoutZapierExcluded(): static
    {
        return $this->reject(
            fn (Field $field) => $field->excludeFromZapierResponse && request()->isZapier()
        )->values();
    }

    /**
     * Apply a filter to exclude the readonly fields from the collection.
     */
    public function withoutReadonly(): static
    {
        return $this->reject(fn (Field $field) => $field->isReadonly());
    }

    /**
     * Add a filter to exclude all non searchable fields.
     */
    public function filterSearchable(): static
    {
        return $this->reject(fn (Field $field) => is_null($field->searchColumn()));
    }

    /**
     * Convert the fields in the collection to searchable columns.
     */
    public function toSearchableColumns(): array
    {
        $columns = [];
        $defaultOperator = 'like';

        $this
            ->filterSearchable()
            ->map(fn (Field $field) => $field->searchColumn())
            ->each(function ($column) use (&$columns, $defaultOperator) {
                if (! is_array($column)) {
                    $columns[$column] = $defaultOperator;
                } else {
                    foreach ($column as $k => $c) {
                        $columns[is_int($k) ? $c : $k] = is_int($k) ? $defaultOperator : $c;
                    }
                }
            });

        return $columns;
    }
}
