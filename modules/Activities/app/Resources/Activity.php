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

namespace Modules\Activities\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Modules\Activities\Criteria\ViewAuthorizedActivitiesCriteria;
use Modules\Activities\Fields\ActivityDueDate;
use Modules\Activities\Fields\ActivityEndDate;
use Modules\Activities\Fields\ActivityType as ActivityTypeField;
use Modules\Activities\Fields\GuestsSelect;
use Modules\Activities\Http\Resources\ActivityResource;
use Modules\Activities\Models\ActivityType;
use Modules\Comments\Contracts\HasComments;
use Modules\Comments\Contracts\PipesComments;
use Modules\Contacts\Fields\Companies;
use Modules\Contacts\Fields\Contacts;
use Modules\Core\Actions\Action;
use Modules\Core\Actions\DeleteAction;
use Modules\Core\Contracts\Resources\Exportable;
use Modules\Core\Contracts\Resources\Importable;
use Modules\Core\Contracts\Resources\Mediable;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\BelongsTo;
use Modules\Core\Fields\Boolean;
use Modules\Core\Fields\CreatedAt;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\Editor;
use Modules\Core\Fields\Heading;
use Modules\Core\Fields\Reminder;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\UpdatedAt;
use Modules\Core\Fields\User;
use Modules\Core\Filters\CreatedAt as CreatedAtFilter;
use Modules\Core\Filters\DateTime as DateTimeFilter;
use Modules\Core\Filters\FilterChildGroup;
use Modules\Core\Filters\FilterGroups;
use Modules\Core\Filters\Radio as RadioFilter;
use Modules\Core\Filters\Select as SelectFilter;
use Modules\Core\Filters\Text as TextFilter;
use Modules\Core\Filters\UpdatedAt as UpdatedAtFilter;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Menu\MenuItem;
use Modules\Core\Models\Model;
use Modules\Core\Models\PinnedTimelineSubject;
use Modules\Core\Resource\Resource;
use Modules\Core\Rules\StringRule;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Table\Column;
use Modules\Core\Table\Table;
use Modules\Deals\Fields\Deals;
use Modules\Users\Filters\UserFilter;
use Modules\Users\Models\User as UserModel;

class Activity extends Resource implements Exportable, HasComments, Importable, Mediable, PipesComments, Tableable, WithResourceRoutes
{
    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'title';

    /**
     * Indicates whether the resource is globally searchable
     */
    public static bool $globallySearchable = true;

    /**
     * Indicates the global search action. (view, float)
     */
    public static string $globalSearchAction = 'float';

    /**
     * The resource displayable icon.
     */
    public static ?string $icon = 'Calendar';

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Activities\Models\Activity';

    /**
     * The attribute to be used when the resource should be displayed.
     */
    public static string $title = 'title';

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make(static::label(), '/activities')
                ->icon(static::$icon)
                ->position(10)
                ->inQuickCreate()
                ->keyboardShortcutChar('A')
                ->singularName(static::singularLabel()),
        ];
    }

    /**
     * Provide the resource table class instance.
     */
    public function table(Builder $query, ResourceRequest $request, string $identifier): Table
    {
        return Table::make($query, $request, $identifier)
            ->withActionsColumn()
            ->select([
                'user_id', // is for the policy checks
                'completed_at', // see appends below
                'due_time', // for displaying in the due date column
                'end_time', // for displaying in the due date column
            ])
            ->appends([
                'is_completed', // for state change
                'is_due', // row class
            ])
            ->withViews()
            ->withDefaultView(
                name: 'activities::activity.views.all',
                flag: 'all-activities',
            )
            ->withDefaultView(
                name: 'activities::activity.views.open',
                flag: 'open-activities',
                rules: [RadioFilter::make('is_completed')->setOperator('equal')->setValue('0')->toArray()]
            )
            ->withDefaultView(
                name: 'activities::activity.views.due_today',
                flag: 'due-today-activities',
                rules: new FilterGroups([
                    new FilterChildGroup(rules: [
                        RadioFilter::make('is_completed')->setOperator('equal')->setValue('0'),
                    ]),
                    new FilterChildGroup(rules: [
                        DateTimeFilter::make('due_date')->setOperator('is')->setValue('today'),
                    ], quick: true),
                ])
            )
            ->withDefaultView(
                name: 'activities::activity.views.due_this_week',
                flag: 'due-this-week-activities',
                rules: new FilterGroups([
                    new FilterChildGroup(rules: [
                        RadioFilter::make('is_completed')->setOperator('equal')->setValue('0'),
                    ]),
                    new FilterChildGroup(rules: [
                        DateTimeFilter::make('due_date')->setOperator('is')->setValue('this_week'),
                    ], quick: true),
                ])
            )
            ->withDefaultView(
                name: 'activities::activity.views.overdue',
                flag: 'overdue-activities',
                rules: new FilterGroups([
                    new FilterChildGroup(rules: [
                        RadioFilter::make('is_completed')->setOperator('equal')->setValue('0'),
                    ]),
                    new FilterChildGroup(rules: [
                        DateTimeFilter::make('due_date')->setOperator('is')->setValue('past'),
                    ], quick: true),
                ])
            )
            ->rowBorderVariant(function (array $row) {
                if ($row['is_due']) {
                    return 'warning';
                } elseif ($row['is_completed']) {
                    return 'success';
                }

                return null;
            })
            // Policy
            ->with('guests')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Prepare global search query.
     */
    public function globalSearchQuery(ResourceRequest $request): Builder
    {
        return parent::globalSearchQuery($request)->select(['id', 'title', 'created_at']);
    }

    /**
     * Provides the resource available CRUD fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            Text::make('title', __('activities::activity.title'))
                ->primary()
                ->withMeta(['attributes' => ['placeholder' => __('activities::activity.title')]])
                ->tapIndexColumn(fn (Column $column) => $column
                    ->width('300px')->minWidth('200px')
                    ->primary()
                    ->route(! $column->isForTrashedTable() ? '/activities/{id}/edit' : '')
                )
                ->rules(StringRule::make())
                ->creationRules('required')
                ->updateRules('filled')
                ->required(true),

            ActivityTypeField::make()
                ->primary()
                ->rules('filled')
                ->required(is_null(ActivityType::getDefaultType()))
                ->creationRules($isTypeRequiredRule = Rule::requiredIf(is_null(ActivityType::getDefaultType())))
                ->importRules($isTypeRequiredRule),

            ActivityDueDate::make(__('activities::activity.due_date'))
                ->tapIndexColumn(fn (Column $column) => $column->queryWhenHidden()) // for row class
                ->width('half')
                ->rules('required_with:due_time')
                ->creationRules('required')
                ->importRules('required')
                ->required(true)
                ->inlineEditWith($this->getDateFieldsForInlineEditing())
                ->updateRules(['required_with:end_date', 'required_with:end_time', 'filled']),

            ActivityEndDate::make(__('activities::activity.end_date'))
                ->tapIndexColumn(fn (Column $column) => $column->queryWhenHidden()) // for due date inlin edit
                ->rules(['required_with:end_time', 'filled'])
                ->updateRules(['required_with:due_date', 'required_with:due_time'])
                ->width('half')
                ->inlineEditWith($this->getDateFieldsForInlineEditing())
                ->hideFromIndex(),

            Reminder::make(
                'reminder_minutes_before',
                __('activities::activity.reminder').($request->isZapier() ? ' (minutes before due)' : '')
            )
                ->searchable(false)
                ->withDefaultValue(30)
                ->help($this->for?->is_reminded ? __('activities::activity.reminder_update_info') : null)
                ->onlyOnForms()
                // Max is 40320 minutes, 4 weeks, as Google events max is 4 weeks
                ->rules(['not_in:0', 'max:40320'])
                ->provideSampleValueUsing(fn () => config('core.defaults.reminder_minutes'))
                ->cancelable(),

            User::make(__('activities::activity.owner'))
                ->primary()
                ->acceptLabelAsValue(false)
                ->withoutClearAction()
                ->creationRules('required')
                ->updateRules('filled')
                ->importRules('required')
                ->required(true)
                ->notification(\Modules\Activities\Notifications\UserAssignedToActivity::class)
                ->trackChangeDate('owner_assigned_date'),

            GuestsSelect::make('guests', __('activities::activity.guests'))
                ->onlyOnForms()
                ->excludeFromExport()
                ->excludeFromImport()
                ->rules(['nullable', 'array']),

            Editor::make('description', __('activities::activity.description'))
                ->rules(['nullable', 'string'])
                ->helpDisplay('text')
                ->help(__('activities::activity.description_info'))
                ->withMeta(['attributes' => ['placeholder' => __('activities::activity.description_info')]])
                ->minimal()
                ->toggleable()
                ->onlyOnForms(),

            DateTime::make('owner_assigned_date', __('activities::activity.owner_assigned_date'))
                ->onlyOnIndex()
                ->excludeFromImport()
                ->hidden(),

            Editor::make('note', __('activities::activity.note'))
                ->withMentions()
                ->minimal()
                ->help(__('activities::activity.note_info'))
                ->helpDisplay('text')
                ->withMeta(['attributes' => ['placeholder' => __('activities::activity.note_info')]])
                ->hideFromIndex()
                ->rules(['nullable', 'string']),

            BelongsTo::make('creator', UserModel::class, __('core::app.created_by'))
                ->excludeFromImport()
                ->onlyOnIndex()
                ->hidden(),

            Heading::make(__('core::resource.associate_with_records'))
                ->excludeFromUpdate(fn () => app(ResourceRequest::class)->viaResource())
                ->excludeFromCreate(fn () => app(ResourceRequest::class)->viaResource())
                ->titleIcon('Link'),

            Contacts::make()
                ->hideFromIndex()
                ->displayOptionViewActionFromPath()
                ->exceptOnForms(fn () => app(ResourceRequest::class)->viaResource()),

            Companies::make()
                ->hideFromIndex()
                ->displayOptionViewActionFromPath()
                ->exceptOnForms(fn () => app(ResourceRequest::class)->viaResource()),

            Deals::make()
                ->hideFromIndex()
                ->excludeFromIndex()
                ->displayOptionViewActionFromPath()
                ->exceptOnForms(fn () => app(ResourceRequest::class)->viaResource()),

            DateTime::make('reminded_at', __('activities::activity.reminder_sent_date'))
                ->onlyOnIndex()
                ->excludeFromImport()
                ->hidden(),

            Boolean::make('is_completed', __('activities::activity.is_completed'))
                ->onlyOnForms()
                ->rules(['nullable', 'boolean'])
                ->searchable(false)
                ->excludeFromImport()
                ->excludeFromExport()
                ->hidden()
                ->fillUsing(function ($model, $attribute, ResourceRequest $request, $value, $requestAttribute) {
                    if (is_null($value)) {
                        return $value;
                    }

                    $markAsCompleted = filter_var($value, FILTER_VALIDATE_BOOLEAN);

                    if (! $model->exists) {
                        $model->completed_at = $markAsCompleted ? now() : null;
                    } else {
                        $isCompleted = $model->isCompleted;

                        if (! $isCompleted && $markAsCompleted === true) {
                            $model->completed_at = now();
                        } elseif ($isCompleted && $markAsCompleted === false) {
                            $model->completed_at = null;
                        }
                    }
                }),

            DateTime::make('completed_at', __('activities::activity.completed_at'))
                ->tapIndexColumn(fn (Column $column) => $column->queryWhenHidden())
                ->onlyOnIndex()
                ->excludeFromImport()
                ->hidden(),

            CreatedAt::make()->hidden(),

            UpdatedAt::make()->hidden(),
        ];
    }

    protected function getDateFieldsForInlineEditing(): array
    {
        return [
            ActivityDueDate::make(__('activities::activity.due_date'))
                ->rules('required_with:due_time')
                ->hideLabel(false),
            ActivityEndDate::make(__('activities::activity.end_date'))
                ->rules(['required_with:end_time', 'filled'])
                ->updateRules(['required_with:due_date', 'required_with:due_time'])
                ->hideLabel(false),
        ];
    }

    /**
     * Get the resource available filters
     */
    public function filters(ResourceRequest $request): array
    {
        return [
            TextFilter::make('title', __('activities::activity.title'))->withoutNullOperators(),
            UserFilter::make(__('activities::activity.owner'))->inQuickFilter()->withoutNullOperators(),
            DateTimeFilter::make('owner_assigned_date', __('activities::activity.owner_assigned_date')),

            SelectFilter::make('activity_type_id', __('activities::activity.type.type'))
                ->valueKey('id')
                ->labelKey('name')
                ->options(function () {
                    return ActivityType::get(['id', 'name', 'swatch_color'])->map(fn (ActivityType $type) => [
                        'id' => $type->id,
                        'name' => $type->name,
                        'swatch_color' => $type->swatch_color,
                    ]);
                })->inQuickFilter(multiple: true),

            RadioFilter::make('is_completed', __('activities::activity.is_completed'))
                ->options([
                    true => __('core::app.yes'),
                    false => __('core::app.no'),
                ])->applyQueryUsing(function (Builder $query, string $boolean, RadioFilter $filter) {
                    $method = $filter->getValue() ? 'completed' : 'incomplete';

                    return $query->{$method}($boolean);
                }),

            DateTimeFilter::make(
                'due_date',
                __('activities::activity.due_date')
            )->column(static::$model::dueDateQueryExpression())->inQuickFilter(),

            DateTimeFilter::make(
                'end_date',
                __('activities::activity.end_date')
            )->column(static::$model::dueDateQueryExpression()),

            DateTimeFilter::make('reminder_at', __('activities::activity.reminder')),
            UserFilter::make(__('core::app.created_by'), 'created_by')->withoutNullOperators()->canSeeWhen('view all activities'),
            CreatedAtFilter::make()->inQuickFilter(),
            UpdatedAtFilter::make(),
        ];
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): string
    {
        return ViewAuthorizedActivitiesCriteria::class;
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return ActivityResource::class;
    }

    /**
     * Provides the resource available actions
     */
    public function actions(ResourceRequest $request): array
    {
        return [
            (new \Modules\Activities\Actions\MarkActivityAsComplete)->onlyOnIndex(),
            (new \Modules\Activities\Actions\DownloadIcsFile)->sole(),

            new \Modules\Core\Actions\BulkEditAction($this),

            Action::make()->floatResourceInEditMode(),

            DeleteAction::make()->canRun(function (ActionRequest $request, Model $model, int $total) {
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
            (new \Modules\Activities\Cards\MyActivities)
                ->help(__('activities::activity.cards.my_activities_info'))
                // Only for refresh events, float is handled in "MyActivitiesCard.vue"
                ->floatResourceInEditMode(static::name()),
            (new \Modules\Activities\Cards\UpcomingUserActivities)
                ->help(__('activities::activity.cards.upcoming_info'))
                ->floatResourceInEditMode(static::name()),
            (new \Modules\Activities\Cards\ActivitiesCreatedBySaleAgent)
                ->canSeeWhen('view all activities')
                ->color('success')
                ->help(__('activities::activity.cards.created_by_agent_info')),
        ];
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return __('activities::activity.activity');
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return __('activities::activity.activities');
    }

    /**
     * Get the resource relationship name when it's associated
     */
    public function associateableName(): string
    {
        return 'activities';
    }

    /**
     * Create the query when the resource is associated and the data is intended for the timeline.
     */
    public function timelineQuery(Model $subject, ResourceRequest $request): Builder
    {
        return parent::timelineQuery($subject, $request)
            ->with('guests.guestable')
            ->reorder()
            // Pinned are always first, then the non-completed sorted by due date asc
            ->orderBy((new PinnedTimelineSubject)->getQualifiedCreatedAtColumn(), 'desc')
            ->orderBy('completed_at', 'asc')
            ->orderBy(static::$model::dueDateQueryExpression(), 'asc')
            ->criteria($this->viewAuthorizedRecordsCriteria());
    }

    /**
     * Register permissions for the resource
     */
    public function registerPermissions(): void
    {
        $this->registerCommonPermissions();

        Innoclapps::permissions(function ($manager) {
            $manager->group($this->name(), function ($manager) {
                $manager->view('view', [
                    'permissions' => [
                        'view attends and owned activities' => __('activities::activity.permissions.attends_and_owned'),
                    ],
                ]);

                $manager->view('export', [
                    'permissions' => [
                        'export activities' => __('core::app.export.export'),
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
            SettingsMenuItem::make($this->name(), __('activities::activity.activities'))
                ->path('/activities')
                ->icon('Calendar')
                ->order(21),
        ];
    }
}
