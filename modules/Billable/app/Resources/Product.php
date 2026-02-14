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

namespace Modules\Billable\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Modules\Billable\Cards\ProductPerformance;
use Modules\Billable\Criteria\ViewAuthorizedProductsCriteria;
use Modules\Billable\Http\Resources\ProductResource;
use Modules\Billable\Models\BillableProduct;
use Modules\Core\Actions\Action;
use Modules\Core\Actions\CloneAction;
use Modules\Core\Actions\DeleteAction;
use Modules\Core\Contracts\Resources\AcceptsCustomFields;
use Modules\Core\Contracts\Resources\AcceptsUniqueCustomFields;
use Modules\Core\Contracts\Resources\Cloneable;
use Modules\Core\Contracts\Resources\Exportable;
use Modules\Core\Contracts\Resources\Importable;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\Boolean;
use Modules\Core\Fields\CreatedAt;
use Modules\Core\Fields\Numeric;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\Textarea;
use Modules\Core\Fields\UpdatedAt;
use Modules\Core\Fields\User;
use Modules\Core\Filters\CreatedAt as CreatedAtFilter;
use Modules\Core\Filters\Number as NumberFilter;
use Modules\Core\Filters\Numeric as NumericFilter;
use Modules\Core\Filters\Radio as RadioFilter;
use Modules\Core\Filters\Text as TextFilter;
use Modules\Core\Filters\UpdatedAt as UpdatedAtFilter;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Menu\MenuItem;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Import\Import;
use Modules\Core\Resource\Resource;
use Modules\Core\Rules\StringRule;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Table\Column;
use Modules\Core\Table\Table;
use Modules\Users\Filters\UserFilter;

class Product extends Resource implements AcceptsCustomFields, AcceptsUniqueCustomFields, Cloneable, Exportable, Importable, Tableable, WithResourceRoutes
{
    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * Indicates whether the resource is globally searchable
     */
    public static bool $globallySearchable = true;

    /**
     * The resource displayable icon.
     */
    public static ?string $icon = 'Bars3CenterLeft';

    /**
     * Indicates whether the resource fields are customizeable
     */
    public static bool $fieldsCustomizable = true;

    /**
     * The attribute to be used when the resource should be displayed.
     */
    public static string $title = 'name';

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Billable\Models\Product';

    /**
     * Provide the resource table class instance.
     */
    public function table(Builder $query, ResourceRequest $request, string $identifier): Table
    {
        return Table::make($query, $request, $identifier)
            ->withActionsColumn()
            ->withViews()
            ->withDefaultView(name: 'billable::product.views.all', flag: 'all-products')
            ->withDefaultView(
                name: 'billable::product.views.active',
                flag: 'active-products',
                rules: [RadioFilter::make('is_active')->setOperator('equal')->setValue('1')->toArray()],
            )
            ->select('created_by') // created_by is for the policy checks
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc');
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return ProductResource::class;
    }

    /**
     * Get the resource importable class
     */
    public function importable(): Import
    {
        return parent::importable()->lookupForDuplicatesUsing(function ($request) {
            return $this->newQueryWithTrashed()
                ->where(function (Builder $query) use ($request) {
                    $query->orWhere(array_filter([
                        'name' => $request->name,
                        'sku' => $request->sku,
                    ]));
                })->first();
        });
    }

    /**
     * Set the available resource fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            Text::make('name', __('billable::product.name'))
                ->rules(StringRule::make())
                ->creationRules('required')
                ->updateRules('filled')
                ->importRules('required')
                ->unique(static::$model)
                ->primary()
                ->required(true)
                ->tapIndexColumn(fn (Column $column) => $column
                    ->width('300px')->minWidth('200px')
                    ->primary()
                    ->route(! $column->isForTrashedTable() ? '/products/{id}/edit' : '')
                ),

            Text::make('sku', __('billable::product.sku'))
                ->unique(static::$model)
                ->useSearchColumn(['sku' => '='])
                ->validationMessages([
                    'unique' => __('billable::product.validation.sku.unique'),
                ])
                ->rules(['nullable', StringRule::make()]),

            Textarea::make('description', __('billable::product.description'))
                ->rules(['nullable', 'string'])
                ->onlyOnForms(),

            Numeric::make('unit_price', __('billable::product.unit_price'))
                ->creationRules($creationRules = ['required', 'numeric', 'decimal:0,3', 'min:0'])
                ->updateRules(['sometimes', ...$creationRules])
                ->importRules('required')
                ->currency()
                ->primary()
                ->width('half'),

            Numeric::make('direct_cost', __('billable::product.direct_cost'))
                ->width('half')
                ->rules(['nullable', 'numeric', 'decimal:0,3', 'min:0'])
                ->tapIndexColumn(fn (Column $column) => $column->hidden())
                ->currency(),

            Numeric::make('tax_rate', __('billable::product.tax_rate'))
                ->withDefaultValue(fn () => BillableProduct::defaultTaxRate())
                ->precision(3)
                ->appendText('%')
                ->rules(['nullable', 'numeric', 'decimal:0,3', 'min:0', 'max:100'])
                ->withMeta(['attributes' => ['max' => 100]])
                ->provideSampleValueUsing(fn () => Arr::random([10, 18, 20]))
                ->width('half')
                ->fillUsing(function (Model $model, string $attribute, ResourceRequest $request, mixed $value, string $requestAttribute) {
                    $value = (is_int($value) || is_float($value) || (is_numeric($value) && ! empty($value))) ? $value : 0;

                    $model->{$attribute} = $value;
                })
                ->primary(),

            Text::make('tax_label', __('billable::product.tax_label'))
                ->withDefaultValue(BillableProduct::defaultTaxLabel())
                ->hideFromIndex()
                ->primary()
                ->width('half')
                ->rules(['nullable', 'string']),

            Text::make('unit', __('billable::product.unit'))
                ->provideSampleValueUsing(fn () => Arr::random(['kg', 'lot']))
                ->rules(['nullable', 'string'])
                ->hideFromIndex(),

            Boolean::make('is_active', __('billable::product.is_active'))
                ->rules(['nullable', 'boolean'])
                ->withDefaultValue(true)
                ->primary()
                ->excludeFromExport(),

            User::make(__('core::app.created_by'), 'creator', 'created_by')
                ->onlyOnIndex()
                ->excludeFromImport()
                ->hidden(),

            CreatedAt::make()->hidden(),

            UpdatedAt::make()->hidden(),
        ];
    }

    /**
     * Get the resource available filters
     */
    public function filters(ResourceRequest $request): array
    {
        return [
            TextFilter::make('name', __('billable::product.name'))->withoutNullOperators(),
            TextFilter::make('sku', __('billable::product.sku')),
            NumericFilter::make('unit_price', __('billable::product.unit_price')),
            NumericFilter::make('direct_cost', __('billable::product.direct_cost')),
            NumberFilter::make('tax_rate', __('billable::product.tax_rate')),
            TextFilter::make('tax_label', __('billable::product.tax_label')),
            TextFilter::make('unit', __('billable::product.unit')),
            RadioFilter::make('is_active', __('billable::product.is_active'))->options([
                true => __('core::app.yes'),
                false => __('core::app.no'),
            ]),
            UserFilter::make(__('core::app.created_by'), 'created_by')->withoutNullOperators()->canSeeWhen('view all products'),
            CreatedAtFilter::make()->inQuickFilter(),
            UpdatedAtFilter::make(),
        ];
    }

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make(static::label(), '/products')
                ->icon(static::$icon)
                ->position(45)
                ->inQuickCreate()
                ->keyboardShortcutChar('P')
                ->singularName(static::singularLabel()),
        ];
    }

    /**
     * Provides the resource available actions
     */
    public function actions(ResourceRequest $request): array
    {
        return [
            new \Modules\Core\Actions\BulkEditAction($this),

            Action::using('mark-as-active', __('billable::product.actions.mark_as_active'), function (Collection $models) {
                $models->each->update(['is_active' => true]);
            })->canRun(
                fn (ActionRequest $request, $model) => $request->user()->can('update', $model)
            )->withoutConfirmation(),

            Action::using('mark-as-inactive', __('billable::product.actions.mark_as_inactive'), function (Collection $models) {
                $models->each->update(['is_active' => false]);
            })->canRun(
                fn (ActionRequest $request, $model) => $request->user()->can('update', $model)
            )->withoutConfirmation(),

            CloneAction::make()->onlyInline(),

            DeleteAction::make()->onlyOnIndex()->canRun(function (ActionRequest $request, Model $model, int $total) {
                return $request->user()->can($total > 1 ? 'bulkDelete' : 'delete', $model);
            })->showInline()->withSoftDeletes(),
        ];
    }

    /**
     * Get the resource available cards
     */
    public function cards(): array
    {
        return [
            (new ProductPerformance)->onlyOnDashboard(),
        ];
    }

    /**
     * Clone the given resource model.
     */
    public function clone(Model $model, int $userId): Model
    {
        return $model->clone($userId);
    }

    /**
     * Prepare global search query.
     */
    public function globalSearchQuery(ResourceRequest $request): Builder
    {
        return parent::globalSearchQuery($request)->select(['id', 'name', 'created_at']);
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): string
    {
        return ViewAuthorizedProductsCriteria::class;
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return __('billable::product.product');
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return __('billable::product.products');
    }

    /**
     * Register permissions for the resource
     */
    public function registerPermissions(): void
    {
        $this->registerCommonPermissions();

        Innoclapps::permissions(function ($manager) {
            $manager->group($this->name(), function ($manager) {
                $manager->view('export', [
                    'permissions' => [
                        'export products' => __('core::app.export.export'),
                    ],
                ]);
            });
        });
    }

    /**
     * Register the settings menu items for the resource
     */
    public function settingsMenu(): array
    {
        return [
            SettingsMenuItem::make($this->name(), __('billable::product.products'))
                ->path('/products')
                ->icon('Bars3CenterLeft')
                ->order(23),
        ];
    }
}
