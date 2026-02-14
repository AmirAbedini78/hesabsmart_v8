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
use Modules\Contacts\Http\Resources\ContactResource;
use Modules\Contacts\Models\Contact as ContactModel;
use Modules\Contacts\Resources\Contact;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\ConfiguresOptions;
use Modules\Core\Fields\Field;
use Modules\Core\Fields\Selectable;
use Modules\Core\Http\Requests\CreateResourceRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Import\Import;
use Modules\Core\Settings\Utilities\Arr;
use Modules\Core\Support\HasOptions;
use Modules\Core\Table\MorphToManyColumn;

class Contacts extends Field
{
    use ConfiguresOptions, HasOptions, Selectable;

    protected static $component = 'select-multiple-field';

    public bool $excludeFromIndexQuery = true;

    public ?int $order = 1000;

    public array $with = ['contacts'];

    protected bool $searchable = false;

    protected static Contact $resource;

    /**
     * Create new instance of Contacts field.
     *
     * @param  string|null  $label
     */
    public function __construct($label = null)
    {
        parent::__construct('contacts', $label ?? __('contacts::contact.contacts'));

        static::$resource = Innoclapps::resourceByModel(ContactModel::class);

        $this->labelKey('display_name')
            ->valueKey('id')
            // Used for export
            ->displayUsing(
                fn ($model) => $model->contacts->map(
                    fn (ContactModel $contact) => $contact->full_name
                )->implode(', ')
            )
            ->onOptionClick('float', ['resourceName' => static::$resource->name()])
            ->eachOnNewLine()
            ->excludeFromZapierResponse()
            ->async('/contacts/search')
            ->lazyLoad('/contacts', ['order' => 'created_at|desc'])
            ->tapIndexColumn(function (MorphToManyColumn $column) {
                $column
                    ->wrap()
                    ->queryAs(ContactModel::nameQueryExpression('display_name'))
                    ->fillRowDataUsing(function (array &$row, Model $model) use ($column) {
                        $row[$column->attribute] = $model->contacts->map(
                            fn (ContactModel $contact) => $column->toRowData($contact)
                        );
                    });
            })
            ->provideSampleValueUsing(fn () => 'Contact Full Name, john@example.com')
            ->fillUsing(function (Model $model, string $attribute, ResourceRequest $request, mixed $value) {
                return ! is_null($value) ? $this->fillCallback($model, $this->parseValue($value, $request)) : null;
            })->resolveForJsonResourceUsing(function (Model $model, string $attribute) {
                if ($model->relationLoaded(static::$resource->associateableName())) {
                    return [
                        $attribute => ContactResource::collection($this->resolve($model)),
                    ];
                }
            });
    }

    /**
     * Provide the column used for index.
     */
    public function indexColumn(): MorphToManyColumn
    {
        return new MorphToManyColumn(
            static::$resource->associateableName(),
            $this->labelKey,
            $this->label,
        );
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
            $model = $resource->newQueryWithTrashed()
                ->where(ContactModel::nameQueryExpression(), $value)
                ->first() ?: $resource->findByEmail($value, $resource->newQueryWithTrashed());

            if ($model?->trashed()) {
                $model->restore();
            }
        }

        if (! $model && is_string($value) && Import::$running) {
            $isEmail = str_contains($value, '@');
            $firstName = $isEmail ? null : explode(' ', $value)[0] ?? null;
            $lastName = $isEmail ? null : explode(' ', $value)[1] ?? null;
            $email = $isEmail ? $value : null;
            $fields = $resource->resolveFields();

            $createRequest = CreateResourceRequest::createFrom(new CreateResourceRequest)
                ->replace([
                    'first_name' => $firstName ?? $email,
                    'last_name' => $lastName,
                    'email' => $email,
                    'user_id' => $request->user()->id,
                ])->setResource($resource->name())
                ->setFields($fields->whereIn('attribute', ['first_name', 'last_name', 'email', 'user_id'])->values())
                ->setOriginal([]);

            $model = $resource->create(new ContactModel, $createRequest);
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
                    $model->contacts()->attach($models->unique($model->first()->getKeyName()));
                }
            } else {
                $model->contacts()->sync($models);
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
