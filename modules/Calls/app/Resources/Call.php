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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Modules\Calls\Http\Resources\CallOutcomeResource;
use Modules\Calls\Http\Resources\CallResource;
use Modules\Calls\Models\CallOutcome;
use Modules\Comments\Contracts\HasComments;
use Modules\Contacts\Fields\Companies;
use Modules\Contacts\Fields\Contacts;
use Modules\Core\Actions\DeleteAction;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Criteria\ViaRelatedResourcesCriteria;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\BelongsTo;
use Modules\Core\Fields\CreatedAt;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\Editor;
use Modules\Core\Fields\UpdatedAt;
use Modules\Core\Fields\User;
use Modules\Core\Filters\CreatedAt as CreatedAtFilter;
use Modules\Core\Filters\Filter;
use Modules\Core\Filters\HasFilter;
use Modules\Core\Filters\MultiSelect as MultiSelectFilter;
use Modules\Core\Filters\Operand;
use Modules\Core\Filters\OperandFilter;
use Modules\Core\Filters\UpdatedAt as UpdatedAtFilter;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Menu\MenuItem;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Resource;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Table\BelongsToColumn;
use Modules\Core\Table\Column;
use Modules\Core\Table\Table;
use Modules\Deals\Fields\Deals;
use Modules\Users\Filters\UserFilter;

class Call extends Resource implements HasComments, Tableable, WithResourceRoutes
{
    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Calls\Models\Call';

    /**
     * The resource displayable icon.
     */
    public static ?string $icon = 'Phone';

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return CallResource::class;
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): ?string
    {
        if (! auth()->user()->isSuperAdmin()) {
            return ViaRelatedResourcesCriteria::class;
        }

        return null;
    }

    /**
     * Set the available resource fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            BelongsTo::make('outcome', CallOutcome::class, __('calls::call.outcome.outcome'))
                ->rules('numeric')
                ->creationRules('required')
                ->updateRules('filled')
                ->setJsonResource(CallOutcomeResource::class)
                ->showValueWhenUnauthorizedToView() // when viewing related record e.q. deal
                ->options(Innoclapps::resourceByModel(CallOutcome::class))
                ->width('half')
                ->withMeta([
                    'attributes' => [
                        'clearable' => false,
                        'placeholder' => __('calls::call.outcome.select_outcome'),
                    ],
                ])
                ->displayAsBadges()
                ->tapIndexColumn(
                    fn (BelongsToColumn $column) => $column->select('swatch_color')->appends(['swatch_color'])
                ),

            DateTime::make('date', __('calls::call.date'))
                ->withDefaultValue(Carbon::now())
                ->width('half')
                ->creationRules('required')
                ->updateRules('filled'),

            Editor::make('body')
                ->rules('string')
                ->creationRules('required')
                ->updateRules('filled')
                ->label(__('calls::call.call_note'))
                ->hideLabel()
                ->validationMessages(['required' => __('validation.required_without_label')])
                ->withMentions(fn () => app(ResourceRequest::class)->viaResource())
                ->tapIndexColumn(fn (Column $column) => $column->wrap())
                ->minimal()
                ->withMeta([
                    'attributes' => [
                        'placeholder' => __('calls::call.log'),
                    ],
                ]),

            User::make(__('core::app.created_by'))->onlyOnIndex(),

            Contacts::make()->onlyOnIndex(),

            Companies::make()->onlyOnIndex()->hidden(),

            Deals::make()->onlyOnIndex()->hidden(),

            CreatedAt::make()->hidden(),

            UpdatedAt::make()->hidden(),
        ];
    }

    /**
     * Get the resource available Filters
     */
    public function filters(ResourceRequest $request): array
    {
        return [
            MultiSelectFilter::make('call_outcome_id', __('calls::call.outcome.outcome'))
                ->valueKey('id')
                ->labelKey('name')
                ->options(function () {
                    return CallOutcome::get(['id', 'name', 'swatch_color'])->map(fn (CallOutcome $type) => [
                        'id' => $type->id,
                        'name' => $type->name,
                        'swatch_color' => $type->swatch_color,
                    ]);
                })->inQuickFilter(multiple: true),

            UserFilter::make()->withoutNullOperators(),

            CreatedAtFilter::make('date', __('calls::call.date'))->inQuickFilter(),

            HasFilter::make('contacts', __('contacts::contact.contact'))->setOperands(
                fn () => Innoclapps::resourceByName('contacts')
                    ->resolveFilters($request)
                    ->reject(fn (Filter $filter) => $filter instanceof OperandFilter)
                    ->map(fn (Filter $filter) => Operand::from($filter))
                    ->values()
                    ->all()
            ),

            HasFilter::make('deals', __('deals::deal.deal'))->setOperands(
                fn () => Innoclapps::resourceByName('deals')
                    ->resolveFilters($request)
                    ->reject(fn (Filter $filter) => $filter instanceof OperandFilter)
                    ->map(fn (Filter $filter) => Operand::from($filter))
                    ->values()
                    ->all()
            ),

            HasFilter::make('companies', __('contacts::company.company'))->setOperands(
                fn () => Innoclapps::resourceByName('companies')
                    ->resolveFilters($request)
                    ->reject(fn (Filter $filter) => $filter instanceof OperandFilter)
                    ->map(fn (Filter $filter) => Operand::from($filter))
                    ->values()
                    ->all()
            ),

            CreatedAtFilter::make(),

            UpdatedAtFilter::make(),
        ];
    }

    /**
     * Provides the resource available actions
     */
    public function actions(ResourceRequest $request): array
    {
        return [
            DeleteAction::make()->canRun(function (ActionRequest $request, Model $model) {
                return $request->user()->can('delete', $model);
            })->showInline(),
        ];
    }

    /**
     * Get the resource available cards
     */
    public function cards(): array
    {
        return [
            (new \Modules\Calls\Cards\LoggedCallsByDay)->withUserSelection()->canSeeWhen('is-super-admin'),
            (new \Modules\Calls\Cards\TotalLoggedCallsBySaleAgent)->canSeeWhen('is-super-admin')->color('success'),
            (new \Modules\Calls\Cards\LoggedCalls)->canSeeWhen('is-super-admin')->withUserSelection(),
            (new \Modules\Calls\Cards\OverviewByCallOutcome)->color('info')->withUserSelection(function () {
                return auth()->user()->isSuperAdmin();
            }),
        ];
    }

    /**
     * Provide the resource table class instance.
     */
    public function table(Builder $query, ResourceRequest $request, string $identifier): Table
    {
        return Table::make($query, $request, $identifier)
            ->withActionsColumn()
            ->withViews()
            ->withDefaultView(name: 'calls::call.views.all', flag: 'all-calls')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make(static::label(), '/calls')
                ->icon(static::$icon)
                ->position(55)
                ->singularName(static::singularLabel()),
        ];
    }

    /**
     * Get the resource relationship name when it's associated
     */
    public function associateableName(): string
    {
        return 'calls';
    }

    /**
     * Get the resource rules available for create and update
     */
    public function rules(ResourceRequest $request): array
    {
        return [
            'via_resource' => ['sometimes', 'required', 'in:contacts,companies,deals', 'string'],
            'via_resource_id' => ['sometimes', 'required', 'numeric'],
        ];
    }

    /**
     * Register the settings menu items for the resource
     */
    public function settingsMenu(): array
    {
        return [
            SettingsMenuItem::make($this->name(), __('calls::call.calls'))
                ->path('/calls')
                ->icon('DeviceMobile')
                ->order(25),
        ];
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return __('calls::call.calls');
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return __('calls::call.call');
    }
}
