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

namespace Modules\Contacts\Resources;

use Illuminate\Database\Eloquent\Builder;
use Modules\Activities\Actions\CreateRelatedActivityAction;
use Modules\Activities\Fields\NextActivityDate;
use Modules\Activities\Filters\ResourceActivitiesFilter;
use Modules\Comments\Contracts\PipesComments;
use Modules\Contacts\Cards\CompaniesByDay;
use Modules\Contacts\Cards\CompaniesBySource;
use Modules\Contacts\Criteria\ViewAuthorizedCompaniesCriteria;
use Modules\Contacts\Fields\Company as CompanyField;
use Modules\Contacts\Fields\Contacts;
use Modules\Contacts\Fields\Phone;
use Modules\Contacts\Fields\Source;
use Modules\Contacts\Filters\AddressOperandFilter;
use Modules\Contacts\Filters\SourceFilter;
use Modules\Contacts\Http\Resources\CompanyResource;
use Modules\Contacts\Http\Resources\IndustryResource;
use Modules\Contacts\Models\Company as CompanyModel;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\Industry;
use Modules\Core\Actions\Action;
use Modules\Core\Actions\DeleteAction;
use Modules\Core\Contracts\Resources\AcceptsCustomFields;
use Modules\Core\Contracts\Resources\AcceptsUniqueCustomFields;
use Modules\Core\Contracts\Resources\Exportable;
use Modules\Core\Contracts\Resources\HasEmail;
use Modules\Core\Contracts\Resources\Importable;
use Modules\Core\Contracts\Resources\Mediable;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Facades\Fields;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\BelongsTo;
use Modules\Core\Fields\Country;
use Modules\Core\Fields\CreatedAt;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\Domain;
use Modules\Core\Fields\Email;
use Modules\Core\Fields\ID;
use Modules\Core\Fields\RelationshipCount;
use Modules\Core\Fields\Tags;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\UpdatedAt;
use Modules\Core\Fields\User;
use Modules\Core\Filters\CreatedAt as CreatedAtFilter;
use Modules\Core\Filters\DateTime as DateTimeFilter;
use Modules\Core\Filters\Filter;
use Modules\Core\Filters\FilterChildGroup;
use Modules\Core\Filters\FilterGroups;
use Modules\Core\Filters\HasFilter;
use Modules\Core\Filters\Operand;
use Modules\Core\Filters\OperandFilter;
use Modules\Core\Filters\Select as SelectFilter;
use Modules\Core\Filters\Tags as TagsFilter;
use Modules\Core\Filters\Text as TextFilter;
use Modules\Core\Filters\UpdatedAt as UpdatedAtFilter;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ImportRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Menu\MenuItem;
use Modules\Core\Models\Model;
use Modules\Core\Pages\Panel;
use Modules\Core\Pages\Tab;
use Modules\Core\Resource\Import\Import;
use Modules\Core\Resource\Resource;
use Modules\Core\Rules\StringRule;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Support\CountryCallingCode;
use Modules\Core\Table\Column;
use Modules\Core\Table\Table;
use Modules\Deals\Fields\Deals;
use Modules\Deals\Filters\ResourceDealsFilter;
use Modules\Documents\Filters\ResourceDocumentsFilter;
use Modules\MailClient\Filters\ResourceEmailsFilter;
use Modules\Notes\Fields\ImportNote;
use Modules\Users\Filters\ResourceUserTeamFilter;
use Modules\Users\Filters\UserFilter;

class Company extends Resource implements AcceptsCustomFields, AcceptsUniqueCustomFields, Exportable, HasEmail, Importable, Mediable, PipesComments, Tableable, WithResourceRoutes
{
    /**
     * Indicates whether the resource has Zapier hooks
     */
    public static bool $hasZapierHooks = true;

    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * Indicates whether the resource has detail view.
     */
    public static bool $hasDetailView = true;

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
    public static ?string $icon = 'OfficeBuilding';

    /**
     * Indicates whether the resource fields are customizeable
     */
    public static bool $fieldsCustomizable = true;

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Contacts\Models\Company';

    /**
     * The attribute to be used when the resource should be displayed.
     */
    public static string $title = 'name';

    /**
     * Get the resource model email address field name
     */
    public function emailAddressField(): string
    {
        return 'email';
    }

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make(static::label(), '/companies')
                ->icon(static::$icon)
                ->position(30)
                ->inQuickCreate()
                ->keyboardShortcutChar('O')
                ->singularName(static::singularLabel()),
        ];
    }

    /**
     * Get the resource relationship name when it's associated
     */
    public function associateableName(): string
    {
        return 'companies';
    }

    /**
     * Get the resource available cards
     */
    public function cards(): array
    {
        return [
            (new CompaniesByDay)->refreshOnActionExecuted()->help(__('core::app.cards.creation_date_info')),
            (new CompaniesBySource)->refreshOnActionExecuted()->help(__('core::app.cards.creation_date_info'))->color('info'),
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
                'user_id', // is for the policy checks,
            ])
            ->withViews()
            ->withDefaultView(
                name: 'contacts::company.views.all',
                flag: 'all-companies',
            )
            ->withDefaultView(
                name: 'contacts::company.views.my',
                flag: 'my-companies',
                rules: new FilterGroups(new FilterChildGroup(rules: [
                    UserFilter::make()->setOperator('equal')->setValue('me'),
                ], quick: true))
            )
            ->withDefaultView(
                name: 'contacts::company.views.my_recently_assigned',
                flag: 'my-recently-assigned-companies',
                rules: new FilterGroups([
                    new FilterChildGroup(rules: [
                        DateTimeFilter::make('owner_assigned_date')->setOperator('is')->setValue('this_month'),
                    ]),
                    new FilterChildGroup(rules: [
                        UserFilter::make()->setOperator('equal')->setValue('me'),
                    ], quick: true),
                ])
            )
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return CompanyResource::class;
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): string
    {
        return ViewAuthorizedCompaniesCriteria::class;
    }

    /**
     * Provides the resource available CRUD fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            ID::make()->hidden(),

            Text::make('name', __('contacts::fields.companies.name'))
                ->tapIndexColumn(fn (Column $column) => $column
                    ->width('300px')->minWidth('200px')
                    ->primary()
                    ->route(! $column->isForTrashedTable() ? '/companies/{id}' : '')
                )
                ->checkPossibleDuplicatesWith(
                    '/companies/search', ['search_fields' => 'name'], 'contacts::company.possible_duplicate'
                )
                ->rules(StringRule::make())
                ->creationRules('required')
                ->updateRules('filled')
                ->importRules('required')
                ->required(true)
                ->hideFromDetail()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->primary(),

            Domain::make('domain', __('contacts::fields.companies.domain'))
                ->rules(['nullable', StringRule::make()])
                ->hideFromIndex(),

            Email::make('email', __('contacts::fields.companies.email'))
                ->rules(StringRule::make())
                ->unique(CompanyModel::class)
                ->validationMessages([
                    'unique' => __('contacts::company.validation.email.unique'),
                ]),

            BelongsTo::make('industry', Industry::class, __('contacts::fields.companies.industry.name'))
                ->setJsonResource(IndustryResource::class)
                ->options(Innoclapps::resourceByModel(Industry::class))
                ->acceptLabelAsValue()
                ->hidden(),

            Phone::make('phones', __('contacts::fields.companies.phone'))
                ->checkPossibleDuplicatesWith(
                    '/companies/search', ['search_fields' => 'phones.number'], 'contacts::company.possible_duplicate'
                )
                ->requireCallingPrefix(
                    function (ResourceRequest|ImportRequest $request) {
                        if ((bool) settings('require_calling_prefix_on_phones')) {
                            return $this->for?->country_id ?? $request->country_id ?? CountryCallingCode::guess() ?? true;
                        }
                    }
                ),

            Tags::make()
                ->forType(Contact::TAGS_TYPE)
                ->rules(['sometimes', 'nullable', 'array'])
                ->hideFromDetail()
                ->hideFromIndex()
                ->excludeFromSettings(Fields::DETAIL_VIEW),

            RelationshipCount::make('contacts', __('contacts::contact.total'))->hidden(),

            RelationshipCount::make('deals', __('deals::deal.total'))->hidden(),

            RelationshipCount::make('openDeals', __('deals::deal.open_deals'))
                ->hidden()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('closedDeals', __('deals::deal.closed_deals'))
                ->hidden()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('wonDeals', __('deals::deal.won_deals'))
                ->hidden()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('lostDeals', __('deals::deal.lost_deals'))
                ->hidden()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('unreadEmailsForUser', __('mailclient::inbox.unread_count'))
                ->hidden()
                ->authRequired()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('incompleteActivitiesForUser', __('activities::activity.incomplete_activities'))
                ->hidden()
                ->authRequired()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('documents', __('documents::document.total_documents'))
                ->hidden()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('draftDocuments', __('documents::document.total_draft_documents'))
                ->hidden()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('calls', __('calls::call.total_calls'))->hidden(),

            Source::make()
                ->collapsed()
                ->hideWhenCreating(),

            CompanyField::make('parent', __('contacts::fields.companies.parent.name'), 'parent_company_id')
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating()
                ->excludeFromImport(),

            Text::make('street', __('contacts::fields.companies.street'))
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(['nullable', StringRule::make()]),

            Text::make('city', __('contacts::fields.companies.city'))
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(['nullable', StringRule::make()]),

            Text::make('state', __('contacts::fields.companies.state'))
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(['nullable', StringRule::make()]),

            Text::make('postal_code', __('contacts::fields.companies.postal_code'))
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(['nullable', StringRule::make()]),

            Country::make(__('contacts::fields.companies.country.name'))
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating(),

            User::make(__('contacts::fields.companies.user.name'))
                ->primary() // Primary field to show the owner in the form
                ->acceptLabelAsValue(false)
                ->withMeta(['attributes' => ['placeholder' => __('core::app.no_owner')]])
                ->notification(\Modules\Contacts\Notifications\UserAssignedToCompany::class)
                ->trackChangeDate('owner_assigned_date')
                ->hideFromDetail()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->showValueWhenUnauthorizedToView(),

            Deals::make()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->hideFromDetail()
                ->hideFromIndex()
                ->order(1001),

            Contacts::make()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->hideFromDetail()
                ->hideFromIndex()
                ->order(1002),

            DateTime::make('owner_assigned_date', __('contacts::fields.companies.owner_assigned_date'))
                ->exceptOnForms()
                ->excludeFromSettings()
                ->hidden(),

            DateTime::make('last_contacted_at', __('contacts::fields.last_contacted_at'))
                ->exceptOnForms()
                ->readonly(true)
                ->hidden(),

            NextActivityDate::make(),

            ImportNote::make(),

            CreatedAt::make()->hidden(),

            UpdatedAt::make()->hidden(),
        ];
    }

    /**
     * Get the resource importable class
     */
    public function importable(): Import
    {
        return parent::importable()->lookupForDuplicatesUsing(function ($request) {
            if ($request->filled('email')) {
                if ($company = $this->findByEmail($request->email, $this->newQueryWithTrashed())) {
                    return $company;
                }
            }

            return $this->findByAddress([
                'street' => $request->street,
                'city' => $request->city,
                'postal_code' => $request->postal_code,
                'country_id' => $request->country_id,
            ], $this->newQueryWithTrashed());
        });
    }

    /**
     * Find company by email for the given query.
     */
    public function findByEmail(string $email, Builder $query): ?CompanyModel
    {
        return $query->where('email', $email)->first();
    }

    /**
     * Find company by name for the given query.
     */
    public function findByName(string $name, Builder $query): ?CompanyModel
    {
        return $query->where('name', $name)->first();
    }

    /**
     * Find company by address for the given query.
     */
    public function findByAddress(array $address, Builder $query): ?CompanyModel
    {
        // If any empty, do not perform query as it will be inacurate.
        if (collect($address)->filter(fn ($value) => ! $value)->isEmpty()) {
            return $query->where($address)->first();
        }

        return null;
    }

    /**
     * Get the resource available Filters
     */
    public function filters(ResourceRequest $request): array
    {
        return [
            TextFilter::make('companies.name', __('contacts::fields.companies.name'))->withoutNullOperators(),
            TextFilter::make('domain', __('contacts::fields.companies.domain')),
            TextFilter::make('email', __('contacts::fields.companies.email')),
            UserFilter::make(__('contacts::fields.companies.user.name'))->inQuickFilter(),
            ResourceUserTeamFilter::make(__('users::team.owner_team')),
            DateTimeFilter::make('owner_assigned_date', __('contacts::fields.companies.owner_assigned_date')),
            TagsFilter::make('tags', __('core::tags.tags'))->forType(Contact::TAGS_TYPE)->inQuickFilter(multiple: true),
            ResourceDocumentsFilter::make(),
            ResourceActivitiesFilter::make(),

            SelectFilter::make('industry_id', __('contacts::fields.companies.industry.name'))
                ->labelKey('name')
                ->valueKey('id')
                ->options(fn () => Industry::get(['id', 'name'])),

            SourceFilter::make(),
            AddressOperandFilter::make('companies'),

            HasFilter::make('phones', __('contacts::fields.companies.phone'))->setOperands([
                Operand::from(TextFilter::make('number', __('contacts::fields.companies.phone'))),
            ])->hideOperands()->checkRelationExistence(),

            ResourceDealsFilter::make(__('contacts::company.company')),
            ResourceEmailsFilter::make(),
            DateTimeFilter::make('last_contacted_at', __('contacts::fields.last_contacted_at')),
            DateTimeFilter::make('next_activity_date', __('activities::activity.next_activity_date')),
            UserFilter::make(__('core::app.created_by'), 'created_by')->withoutNullOperators()->canSeeWhen('view all companies'),
            CreatedAtFilter::make()->inQuickFilter(),
            UpdatedAtFilter::make(),

            HasFilter::make('contacts', __('contacts::contact.contact'))->setOperands(
                fn () => Innoclapps::resourceByName('contacts')
                    ->resolveFilters($request)
                    ->reject(fn (Filter $filter) => $filter instanceof OperandFilter)
                    ->map(fn (Filter $filter) => Operand::from($filter))
                    ->values()
                    ->all()
            ),
        ];
    }

    /**
     * Provides the resource available actions
     */
    public function actions(ResourceRequest $request): array
    {
        return [
            new \Modules\Core\Actions\SearchInGoogleAction,

            new \Modules\Core\Actions\BulkEditAction($this),

            CreateRelatedActivityAction::make()->onlyInline(),

            Action::make()->floatResourceInEditMode(),

            DeleteAction::make()->canRun(function (ActionRequest $request, Model $model, int $total) {
                return $request->user()->can($total > 1 ? 'bulkDelete' : 'delete', $model);
            })->showInline()->withSoftDeletes(),
        ];
    }

    /**
     * Prepare display query.
     */
    public function displayQuery(): Builder
    {
        return parent::displayQuery()->with([
            'parents',
            'media',
            'contacts.phones', // phones are for calling
            'deals.stage', // stage is for card display on detail
        ]);
    }

    /**
     * Prepare global search query.
     */
    public function globalSearchQuery(ResourceRequest $request): Builder
    {
        return parent::globalSearchQuery($request)->select(
            ['id', 'email', 'name', 'created_at']
        );
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('contacts::company.companies');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('contacts::company.company');
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
                        'export companies' => __('core::app.export.export'),
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
            SettingsMenuItem::make($this->name(), __('contacts::company.companies'))
                ->path('/companies')
                ->icon('OfficeBuilding')
                ->order(24),
        ];
    }

    /**
     * Boot the resource.
     */
    protected function boot(): void
    {
        $this->getDetailPage()->tab(
            Tab::make('timeline', 'timeline-tab')->panel('timeline-tab-panel')->order(10)
        )->panels(function () {
            return [
                Panel::make('company-detail-panel', 'resource-details-panel')
                    ->heading(__('core::app.record_view.sections.details'))
                    ->resizeable(),
                Panel::make('deals', 'resource-deals-panel')->heading(__('deals::deal.deals')),
                Panel::make('contacts', 'resource-contacts-panel')->heading(__('contacts::contact.contacts')),
                Panel::make('media', 'resource-media-panel')->heading(__('core::app.attachments')),
                Panel::make('child', 'company-child-companies-panel')->heading(trans_choice('contacts::company.child', 2)),
            ];
        });
    }
}
