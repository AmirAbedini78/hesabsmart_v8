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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use JsonSerializable;
use Modules\Core\Actions\ResolvesActions;
use Modules\Core\Contracts\Resources\AcceptsUniqueCustomFields;
use Modules\Core\Contracts\Resources\Exportable;
use Modules\Core\Contracts\Resources\Importable;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Criteria\FiltersCriteria;
use Modules\Core\Criteria\RequestCriteria;
use Modules\Core\Facades\Cards;
use Modules\Core\Facades\Fields;
use Modules\Core\Facades\Menu;
use Modules\Core\Facades\SettingsMenu;
use Modules\Core\Fields\CustomFieldCollection;
use Modules\Core\Fields\CustomFieldFactory;
use Modules\Core\Fields\CustomFieldFileCache;
use Modules\Core\Fields\Field;
use Modules\Core\Filters\QueryBuilderGroups;
use Modules\Core\Filters\ResolvesFilters;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\Model;
use Modules\Core\Pages\Page;
use Modules\Core\Pages\StandardDetailPage;
use Modules\Core\Resource\Import\Import;
use Modules\Core\Resource\Import\ImportSample;

abstract class Resource implements JsonSerializable
{
    use Associateable,
        HasHookableMethods,
        QueriesResources,
        ResolvesActions,
        ResolvesFields,
        ResolvesTables;
    use ResolvesFilters {
        ResolvesFilters::resolveFilters as resolveBaseFilters;
    }

    /**
     * The column the records should be default ordered by when retrieving.
     */
    public static string $orderBy = 'id';

    /**
     * The direction the records should be default ordered by when retrieving.
     */
    public static string $orderByDir = 'asc';

    /**
     * Indicates whether the resource is globally searchable.
     */
    public static bool $globallySearchable = false;

    /**
     * The number of records to query when global searching.
     */
    public static int $globalSearchResultsLimit = 10;

    /**
     * Indicates the global search action. (view, float)
     */
    public static string $globalSearchAction = 'view';

    /**
     * The resource displayable icon.
     */
    public static ?string $icon = null;

    /**
     * Indicates whether the resource fields are customizeable.
     */
    public static bool $fieldsCustomizable = false;

    /**
     * Indicates whether the resource has Zapier hooks.
     */
    public static bool $hasZapierHooks = false;

    /**
     * The model the resource is related to.
     *
     * @var \Modules\Core\Models\Model|null
     */
    public static string $model;

    /**
     * The attribute to be used when the resource should be displayed.
     */
    public static string $title = 'id';

    /**
     * The underlying model resource instance.
     *
     * @var \Modules\Core\Models\Model|null
     */
    public $for;

    /**
     * Indicates whether the resource has detail view.
     */
    public static bool $hasDetailView = false;

    protected static array $registered = [];

    protected static array $pages = [];

    /**
     * Initialize new Resource class.
     */
    public function __construct($model = null)
    {
        $this->registerIfNotRegistered();

        if ($model) {
            $this->for($model);
        }
    }

    /**
     * Set the resource model instance.
     *
     * @param  \Modules\Core\Models\Model|null  $model
     */
    public function for($model)
    {
        $this->for = $model;

        return $this;
    }

    /**
     * Get a fresh instance of the resource model.
     *
     * @return \Modules\Core\Models\Model
     */
    public function newModel(array $attributes = [])
    {
        $model = static::$model;

        return new $model($attributes);
    }

    /**
     * Provide the resource available cards
     */
    public function cards(): array
    {
        return [];
    }

    /**
     *  Resolve the filters intended for the resource.
     *
     * @return \Illuminate\Support\Collection<object, \Modules\Core\Filters\Filter>
     */
    public function resolveFilters(ResourceRequest $request)
    {
        return $this->resolveBaseFilters($request)->merge(
            $this->getFiltersFromCustomFields()
        );
    }

    /**
     * Get filters from the resource custom fields.
     */
    public function getFiltersFromCustomFields(): array
    {
        return (new CustomFieldFactory($this->name()))->createFiltersFromFields();
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): ?string
    {
        return null;
    }

    /**
     * Create JSON Resource.
     *
     * @return mixed
     */
    public function createJsonResource(mixed $data, bool $resolve = false, ?ResourceRequest $request = null)
    {
        $collection = is_countable($data);

        if ($collection) {
            $resource = $this->jsonResource()::collection($data);
        } else {
            $jsonResource = $this->jsonResource();
            $resource = new $jsonResource($data);
        }

        if ($resolve) {
            $request = $request ?: app(ResourceRequest::class)->setResource($this->name());

            if (! $collection) {
                $request->setResourceId($data->getKey());
            }

            return $resource->resolve($request);
        }

        return $resource;
    }

    /**
     * Get the fields that should be included in JSON resource.
     *
     * @param  bool  $canSeeResource  Indicates whether the current user can see the model in the JSON resource
     */
    public function getFieldsForJsonResource($canSeeResource = true): array
    {
        $fields = Cache::store('array')->rememberForever($this->name().'json-resource-fields', function () {
            return $this->resolveFields()->withoutZapierExcluded()->all();
        });

        $result = array_filter($fields, function (Field $field) use ($canSeeResource) {
            return $canSeeResource || $field->alwaysInJsonResource === true;
        });

        return array_values($result);

    }

    /**
     * Provide the available resource fields.
     */
    public function fields(ResourceRequest $request): array
    {
        return [];
    }

    /**
     * Provide the resource rules available for create and update.
     */
    public function rules(ResourceRequest $request): array
    {
        return [];
    }

    /**
     * Provide the resource rules available only for create.
     */
    public function createRules(ResourceRequest $request): array
    {
        return [];
    }

    /**
     * Provide the resource rules available only for update.
     */
    public function updateRules(ResourceRequest $request): array
    {
        return [];
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): ?string
    {
        return null;
    }

    /**
     * Provide the menu items for the resource.
     */
    public function menu(): array
    {
        return [];
    }

    /**
     * Provide the settings menu items for the resource.
     */
    public function settingsMenu(): array
    {
        return [];
    }

    /**
     * Register permissions for the resource.
     */
    public function registerPermissions(): void {}

    /**
     * Get the custom validation messages for the resource
     * Useful for resources without fields.
     */
    public function validationMessages(): array
    {
        return [];
    }

    /**
     * Get the resource available custom fields.
     */
    public function customFields(): CustomFieldCollection
    {
        return Cache::store('array')->rememberForever(static::name().'-customfields', function () {
            return CustomFieldFileCache::get()->where('resource_name', static::name());
        });
    }

    /**
     * Get the resource search columns.
     */
    public function searchableColumns(): array
    {
        return $this->resolveFields()->toSearchableColumns();
    }

    /**
     * Get columns that should be used for global search.
     */
    public function globalSearchColumns(): array
    {
        return $this->resolveFields()->filter(function (Field $field) {
            return $field->isCustomField() ? $field->isUnique() : true;
        })->toSearchableColumns();
    }

    /**
     * Get the global search result url.
     */
    public function globalSearchResultViewUrl(Model $model): string
    {
        return $this->viewRouteFor($model);
    }

    /**
     * Get the global search title.
     */
    public function globalSearchTitle(Model $model): string
    {
        return $this->titleFor($model);
    }

    /**
     * Determine if this resource is searchable.
     */
    public function searchable(): bool
    {
        return count($this->searchableColumns()) > 0;
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return Str::plural(Str::title(Str::snake(class_basename(get_called_class()), ' ')));
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return Str::singular(static::label());
    }

    /**
     * Get the internal name of the resource.
     */
    public static function name(): string
    {
        return Str::plural(Str::kebab(class_basename(get_called_class())));
    }

    /**
     * Get the internal singular name of the resource.
     */
    public static function singularName(): string
    {
        return Str::singular(static::name());
    }

    /**
     * Get the resource importable class.
     */
    public function importable(): Import
    {
        return new Import($this);
    }

    /**
     * Get the resource import sample class
     */
    public function importSample(int $totalRows = 1): ImportSample
    {
        return new ImportSample($this, $totalRows);
    }

    /**
     * Get the resource export class.
     */
    public function exportable(Builder $query): Export
    {
        return new Export($this, $query);
    }

    /**
     * Register the resource available menu items.
     */
    protected function registerMenuItems(): void
    {
        Menu::register($this->menu(...));
    }

    /**
     * Register the resource settings menu items.
     */
    protected function registerSettingsMenuItems(): void
    {
        SettingsMenu::register($this->settingsMenu(...));
    }

    /**
     * Register the resource available cards.
     */
    protected function registerCards(): void
    {
        Cards::register($this->name(), $this->cards(...));
    }

    /**
     * Register the resource available fields.
     */
    protected function registerFields(): void
    {
        Fields::group($this->name(), function () {
            $resourceFields = $this->fields(app(ResourceRequest::class)->setResource($this->name()));

            return array_merge($resourceFields, $this->customFields()->map(
                fn (CustomField $field) => CustomFieldFactory::createInstance($field)
            )->all());
        });
    }

    /**
     * Register common permissions for the resource.
     */
    protected function registerCommonPermissions(): void
    {
        if ($callable = config('core.resources.permissions.common')) {
            (new $callable)($this);
        }
    }

    /**
     * Flush the resources state.
     */
    public static function flushState(): void
    {
        static::$registered = [];
        static::$pages = [];
    }

    /**
     * Register the resource if not registered.
     */
    protected function registerIfNotRegistered(): void
    {
        if (! isset(static::$registered[static::class])) {
            $this->register();

            static::$registered[static::class] = true;

            $this->boot();
        }
    }

    /**
     * Boot the resource.
     */
    protected function boot(): void {}

    /**
     * Register the resource data.
     */
    protected function register(): void
    {
        $this->registerPermissions();
        $this->registerCards();

        if ($this instanceof WithResourceRoutes) {
            $this->registerFields();
        }

        $this->registerMenuItems();
        $this->registerSettingsMenuItems();
    }

    /**
     * Create new request criteria for the resource.
     */
    public function createRequestCriteria(ResourceRequest $request, ?array $searchableColumns = null): RequestCriteria
    {
        return (new RequestCriteria($request))->setSearchFields(
            $searchableColumns ?? $this->searchableColumns()
        );
    }

    /**
     * Create new filters criteria for the resource.
     */
    public function createFiltersCriteria(ResourceRequest $request): FiltersCriteria
    {
        return new FiltersCriteria(
            new QueryBuilderGroups($request->get('filters', []), $this->resolveFilters($request)),
            $request
        );
    }

    /**
     * Fill the model from the given request.
     *
     * @param  ResourceRequest&\Modules\Core\Http\Requests\InteractsWithResourceFields  $request
     * @return array{\Modules\Core\Models\Model, array<int, callable>}
     */
    public function fillFields(Model $model, ResourceRequest $request): array
    {
        $callbacks = [];

        $request->toFields()->each(function (Field $field) use ($request, &$model, &$callbacks) {
            $callback = $field->fill(
                $model,
                $field->attribute,
                $request,
                $field->requestAttribute()
            );

            if (is_callable($callback)) {
                $callbacks[] = $callback;
            }
        });

        return [$model, $callbacks];
    }

    /**
     * Create new resource record in storage.
     */
    public function create(Model $model, ResourceRequest $request): Model
    {
        [$model, $callbacks] = $this->fillFields($model, $request);

        $this->beforeCreate($model, $request);

        $model->save();

        DB::afterCommit(function () use ($callbacks, $model, $request) {
            collect($callbacks)->each->__invoke($model, $request);
        });

        $this->afterCreate($model, $request);

        return $model;
    }

    /**
     * Update resource record in storage.
     */
    public function update(Model $model, ResourceRequest $request): Model
    {
        [$model, $callbacks] = $this->fillFields($model, $request);

        $this->beforeUpdate($model, $request);

        $model->save();

        DB::afterCommit(function () use ($callbacks, $model, $request) {
            collect($callbacks)->each->__invoke($model, $request);
        });

        $this->afterUpdate($model, $request);

        return $model;
    }

    /**
     * Delete resource record.
     */
    public function delete(Model $model, ResourceRequest $request): bool
    {
        $this->beforeDelete($model, $request);

        $model->delete();

        $this->afterDelete($model, $request);

        return true;
    }

    /**
     * Force delete the resource record.
     */
    public function forceDelete(Model $model, ResourceRequest $request): void
    {
        $this->beforeDelete($model, $request);

        $model->forceDelete();

        $this->afterDelete($model, $request);
    }

    /**
     * Restore the resource record from trash.
     */
    public function restore(Model $model, ResourceRequest $request): void
    {
        $this->beforeRestore($model, $request);

        $model->restore();

        $this->afterRestore($model, $request);
    }

    /**
     * Get a view route for the given model.
     */
    public function viewRouteFor(Model $model): string
    {
        $name = static::name();

        return "/{$name}/{$model->getKey()}";
    }

    /**
     * Get a title for the given model.
     */
    public function titleFor(Model $model): string
    {
        return (string) data_get($model, static::$title);
    }

    /**
     * Provide the resource detail page.
     */
    protected function detailPage(): Page
    {
        return StandardDetailPage::make(static::name());
    }

    /**
     * Get the resource detail page.
     */
    public function getDetailPage(): Page|StandardDetailPage
    {
        if (isset(static::$pages[static::name()]['detail'])) {
            return static::$pages[static::name()]['detail'];
        }

        return static::$pages[static::name()]['detail'] ??= $this->detailPage();
    }

    /**
     * Serialize the resource
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name(),
            'singularName' => $this->singularName(),
            'label' => $this->label(),
            'singularLabel' => $this->singularLabel(),
            'icon' => static::$icon,
            'detailPage' => $this->getDetailPage(),
            'globallySearchable' => static::$globallySearchable,
            'fieldsCustomizable' => static::$fieldsCustomizable,
            'acceptsUniqueCustomFields' => $this instanceof AcceptsUniqueCustomFields,
            'hasDetailView' => static::$hasDetailView,
            'authorizedToCreate' => Gate::allows('create', static::$model),
            'authorizedToImport' => $this instanceof Importable && Gate::allows('create', static::$model),
            'authorizedToExport' => $this instanceof Exportable && Gate::allows('export', static::$model),
            'usesSoftDeletes' => static::$model::usesSoftDeletes(),
        ];
    }
}
