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

namespace Modules\Contacts\Fields;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Arr;
use Modules\Contacts\Http\Resources\CompanyResource;
use Modules\Contacts\Models\Company as CompanyModel;
use Modules\Contacts\Resources\Company;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\ConfiguresOptions;
use Modules\Core\Fields\Field;
use Modules\Core\Fields\Selectable;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Import\Import;
use Modules\Core\Support\HasOptions;
use Modules\Core\Table\MorphToManyColumn;

class Companies extends Field
{
    use ConfiguresOptions, HasOptions, Selectable;

    protected static $component = 'select-multiple-field';

    public bool $excludeFromIndexQuery = true;

    public ?int $order = 1001;

    public array $with = ['companies'];

    protected bool $searchable = false;

    protected static Company $resource;

    /**
     * Create new instance of Companies field.
     *
     * @param  string|null  $label
     */
    public function __construct($label = null)
    {
        parent::__construct('companies', $label ?? __('contacts::company.companies'));

        static::$resource = Innoclapps::resourceByModel(CompanyModel::class);

        $this->labelKey('name')
            ->valueKey('id')
            // Used for export
            ->displayUsing(
                fn ($model) => $model->companies->pluck('name')->implode(', ')
            )
            ->onOptionClick('float', ['resourceName' => static::$resource->name()])
            ->eachOnNewLine()
            ->excludeFromZapierResponse()
            ->async('/companies/search')
            ->lazyLoad('/companies', ['order' => 'created_at|desc'])
            ->tapIndexColumn(function (MorphToManyColumn $column) {
                $column
                    ->wrap()
                    ->fillRowDataUsing(function (array &$row, Model $model) use ($column) {
                        $row[$column->attribute] = $model->companies->map(
                            fn (CompanyModel $company) => $column->toRowData($company)
                        );
                    });
            })
            ->provideSampleValueUsing(fn () => 'Company Name, Other Company Name')
            ->fillUsing(function (Model $model, string $attribute, ResourceRequest $request, mixed $value) {
                return ! is_null($value) ? $this->fillCallback($model, $this->parseValue($value, $request)) : null;
            })->resolveForJsonResourceUsing(function (Model $model, string $attribute) {
                if ($model->relationLoaded(static::$resource->associateableName())) {
                    return [
                        $attribute => CompanyResource::collection($this->resolve($model)),
                    ];
                }
            });
    }

    /**
     * Provide the column used for index.
     */
    public function indexColumn(): MorphToManyColumn
    {
        return (new MorphToManyColumn(
            static::$resource->associateableName(),
            $this->labelKey,
            $this->label
        ))->wrap(true);
    }

    public function mailableTemplatePlaceholder($model)
    {
        //
    }

    /**
     * Parse the given value for storage.
     */
    protected function parseValue($value, ResourceRequest $request): Collection
    {
        // Perhaps int e.q. when ID provided?
        $value = is_string($value) ? explode(',', $value) : Arr::wrap($value);
        $collection = new Collection([]);

        foreach ($value as $id) {
            if ($model = $this->getModelFromValue($id, $request)) {
                $collection->push($model);
            }
        }

        return $collection;
    }

    /**
     * Get model instance from the given ID and ensure it's authorized to view before syncing.
     */
    protected function getModelFromValue(int|string|null $value, ResourceRequest $request): ?EloquentModel
    {
        $model = null;
        $resource = static::$resource;
        $value = is_string($value) ? trim($value) : $value;

        // ID provided?
        if (is_numeric($value)) {
            $model = $resource->newQuery()->find($value);
        } elseif ($value) {

            $model = $resource->findByName($value, $resource->newQueryWithTrashed());

            if ($model?->trashed()) {
                $model->restore();
            }
        }

        if (! $model && is_string($value) && Import::anyInProgress()) {
            $fields = $resource->resolveFields();

            $createRequest = $request->asCreateRequest([
                'name' => $value,
                'user_id' => $request->user()->id,
            ])
                ->setResource($resource->name())
                ->setFields($fields->whereIn('attribute', ['name', 'user_id'])->values())
                ->setOriginal([]);

            $resource->create(new CompanyModel, $createRequest);
        }

        return $model && $request->user()->can('view', $model) ? $model : null;
    }

    /**
     * Get the fill callback.
     */
    protected function fillCallback(Model $model, Collection $models)
    {
        return function () use ($model, $models) {
            if ($model->wasRecentlyCreated) {
                if (count($models) > 0) {
                    $model->companies()->syncWithoutDetaching($models->unique($model->first()->getKeyName()));
                }
            } else {
                $model->companies()->sync($models);
            }
        };
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'options' => [],
            'labelKey' => $this->labelKey,
            'valueKey' => $this->valueKey,
        ]);
    }
}
