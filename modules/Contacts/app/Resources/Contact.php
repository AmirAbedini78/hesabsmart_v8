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
use Modules\Contacts\Cards\ContactsByDay;
use Modules\Contacts\Cards\ContactsBySource;
use Modules\Contacts\Cards\RecentlyCreatedContacts;
use Modules\Contacts\Criteria\ViewAuthorizedContactsCriteria;
use Modules\Contacts\Fields\Companies;
use Modules\Contacts\Fields\Phone;
use Modules\Contacts\Fields\Source;
use Modules\Contacts\Filters\AddressOperandFilter;
use Modules\Contacts\Filters\SourceFilter;
use Modules\Contacts\Http\Resources\ContactResource;
use Modules\Contacts\Models\Contact as ContactModel;
use Modules\Contacts\Models\Phone as PhoneModel;
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
use Modules\Core\Facades\ChangeLogger;
use Modules\Core\Facades\Fields;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\Country;
use Modules\Core\Fields\CreatedAt;
use Modules\Core\Fields\DateTime;
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

class Contact extends Resource implements AcceptsCustomFields, AcceptsUniqueCustomFields, Exportable, HasEmail, Importable, Mediable, PipesComments, Tableable, WithResourceRoutes
{
    /**
     * Indicates whether the resource has Zapier hooks
     */
    public static bool $hasZapierHooks = true;

    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'first_name';

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
    public static ?string $icon = 'Users';

    /**
     * Indicates whether the resource fields are customizeable
     */
    public static bool $fieldsCustomizable = true;

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Contacts\Models\Contact';

    /**
     * The attribute to be used when the resource should be displayed.
     */
    public static string $title = 'full_name';

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
            MenuItem::make(static::label(), '/contacts')
                ->icon(static::$icon)
                ->position(25)
                ->inQuickCreate()
                ->keyboardShortcutChar('C')
                ->singularName(static::singularLabel()),
        ];
    }

    /**
     * Get the resource relationship name when it's associated
     */
    public function associateableName(): string
    {
        return 'contacts';
    }

    /**
     * Get the resource available cards
     */
    public function cards(): array
    {
        return [
            (new ContactsByDay)->refreshOnActionExecuted()->help(__('core::app.cards.creation_date_info')),
            (new ContactsBySource)->refreshOnActionExecuted()->help(__('core::app.cards.creation_date_info'))->color('info'),
            (new RecentlyCreatedContacts)->onlyOnDashboard()->floatResourceInDetailMode(static::name()),
        ];
    }

    /**
     * Prepare display query.
     */
    public function displayQuery(): Builder
    {
        return parent::displayQuery()->with([
            'media',
            'companies.phones', // phones are for calling
            'deals.stage', // stage is for card display on detail
        ]);
    }

    /**
     * Prepare global search query.
     */
    public function globalSearchQuery(ResourceRequest $request): Builder
    {
        return parent::globalSearchQuery($request)->select(
            ['id', 'email', 'first_name', 'last_name', 'created_at']
        );
    }

    /**
     * Get the resource search columns.
     */
    public function searchableColumns(): array
    {
        return array_merge(
            parent::searchableColumns(),
            ['full_name' => [
                'column' => $this->newModel()->nameQueryExpression(),
                'condition' => 'like',
            ]],
        );
    }

    /**
     * Get columns that should be used for global search.
     */
    public function globalSearchColumns(): array
    {
        return array_merge(
            parent::globalSearchColumns(),
            ['full_name' => [
                'column' => $this->newModel()->nameQueryExpression(),
                'condition' => 'like',
            ]],
        );
    }

    /**
     * Provide the resource table class instance.
     */
    public function table(Builder $query, ResourceRequest $request, string $identifier): Table
    {
        return ContactTable::make($query, $request, $identifier)
            ->withDefaultView(
                name: 'contacts::contact.views.all',
                flag: 'all-contacts',
            )
            ->withDefaultView(
                name: 'contacts::contact.views.my',
                flag: 'my-contacts',
                rules: new FilterGroups(new FilterChildGroup(rules: [
                    UserFilter::make()->setOperator('equal')->setValue('me'),
                ], quick: true))
            )
            ->withDefaultView(
                name: 'contacts::contact.views.my_recently_assigned',
                flag: 'my-recently-assigned-contacts',
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
        return ContactResource::class;
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): string
    {
        return ViewAuthorizedContactsCriteria::class;
    }

    /**
     * Provides the resource available CRUD fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            ID::make()->hidden(),

            $firstName = Text::make('first_name', __('contacts::fields.contacts.first_name'))
                ->primary()
                ->rules(StringRule::make())
                ->creationRules('required')
                ->updateRules('filled')
                ->importRules('required')
                ->hideFromDetail()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->excludeFromIndex()
                ->required(true),

            $lastName = Text::make('last_name', __('contacts::fields.contacts.last_name'))
                ->rules(['nullable', StringRule::make()])
                ->hideFromDetail()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->excludeFromIndex(),

            Text::make('contact', __('contacts::contact.contact'))
                ->searchable(false)
                ->excludeFromExport()
                ->excludeFromImport()
                ->excludeFromZapierResponse()
                ->excludeFromPlaceholders()
                ->onlyOnIndex()
                ->inlineEditWith([$firstName, $lastName])
                ->tapIndexColumn(fn (Column $column) => $column->width('300px')->minWidth('200px')
                    ->primary()
                    ->select($cols = ['first_name', 'last_name']) // for inline editing
                    ->appends($cols)
                    ->route(! $column->isForTrashedTable() ? '/contacts/{id}' : '')
                    ->queryAs(ContactModel::nameQueryExpression('contact'))
                    ->orderByUsing(function (Builder $query, string $direction) {
                        return $query->orderBy(ContactModel::nameQueryExpression(), $direction);
                    })
                ),

            Email::make('email', __('contacts::fields.contacts.email'))
                ->rules(['nullable', StringRule::make(), 'email'])
                ->unique(ContactModel::class)
                ->unique(\Modules\Users\Models\User::class)
                ->validationMessages(['unique' => __('contacts::contact.validation.email.unique')])
                ->showValueWhenUnauthorizedToView(),

            Phone::make('phones', __('contacts::fields.contacts.phone'))
                ->checkPossibleDuplicatesWith(
                    '/contacts/search', ['search_fields' => 'phones.number'], 'contacts::contact.possible_duplicate'
                )
                ->canUnmarkUnique()
                ->unique(ContactModel::class, __('contacts::contact.validation.phone.unique'))
                ->requireCallingPrefix(
                    function (ResourceRequest|ImportRequest $request) {
                        if ((bool) settings('require_calling_prefix_on_phones')) {
                            return $this->for?->country_id ?? $request->country_id ?? CountryCallingCode::guess() ?? true;
                        }
                    }
                ),

            Tags::make()
                ->forType(ContactModel::TAGS_TYPE)
                ->rules(['sometimes', 'nullable', 'array'])
                ->hideFromDetail()
                ->hideFromIndex()
                ->excludeFromSettings(Fields::DETAIL_VIEW),

            User::make(__('contacts::fields.contacts.user.name'))
                ->primary()
                ->acceptLabelAsValue(false)
                ->withMeta(['attributes' => ['placeholder' => __('core::app.no_owner')]])
                ->notification(\Modules\Contacts\Notifications\UserAssignedToContact::class)
                ->trackChangeDate('owner_assigned_date')
                ->hideFromDetail()
                ->tapIndexColumn(fn (Column $column) => $column->queryWhenHidden()) // policy checks
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->showValueWhenUnauthorizedToView(),

            Source::make(),

            Companies::make()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->hideFromDetail()
                ->hideFromIndex()
                ->order(1001),

            Deals::make()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->hideFromDetail()
                ->hideFromIndex()
                ->order(1002),

            DateTime::make('owner_assigned_date', __('contacts::fields.contacts.owner_assigned_date'))
                ->onlyOnIndex()
                ->excludeFromImport()
                ->hidden(),

            RelationshipCount::make('companies', __('contacts::company.total'))->hidden(),

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

            Text::make('job_title', __('contacts::fields.contacts.job_title'))
                ->rules(['nullable', StringRule::make()])
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating(),

            Text::make('street', __('contacts::fields.contacts.street'))
                ->rules(['nullable', StringRule::make()])
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating(),

            Text::make('city', __('contacts::fields.contacts.city'))
                ->rules(['nullable', StringRule::make()])
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating(),

            Text::make('state', __('contacts::fields.contacts.state'))
                ->rules(['nullable', StringRule::make()])
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating(),

            Text::make('postal_code', __('contacts::fields.contacts.postal_code'))
                ->rules(['nullable', StringRule::make()])
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating(),

            Country::make(__('contacts::fields.contacts.country.name'))
                ->collapsed()
                ->hideFromIndex()
                ->hideWhenCreating(),

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
     * Handle the "afterCreate" resource record hook.
     */
    public function afterCreate(Model $model, ResourceRequest $request): void
    {
        if ($model->email && (bool) settings('auto_associate_company_to_contact')) {
            $emailDomain = substr($model->email, strpos($model->email, '@') + 1);

            $request->findResource('companies')
                ->newQuery()
                ->where('domain', $emailDomain)->get()
                ->whenNotEmpty(function ($companies) use ($model) {
                    ChangeLogger::asSystem(fn () => $model->companies()->syncWithoutDetaching($companies->modelKeys()));
                });
        }
    }

    /**
     * Get the resource importable class
     */
    public function importable(): Import
    {
        return parent::importable()->lookupForDuplicatesUsing(function ($request) {
            if ($request->filled('email')) {
                if ($contact = $this->findByEmail($request->email, $this->newQueryWithTrashed())) {
                    return $contact;
                }
            }

            if ($request->filled('phones')) {
                return $this->findByPhones($request->phones);
            }
        });
    }

    /**
     * Find contact by email for the given query.
     */
    public function findByEmail(string $email, Builder $query): ?ContactModel
    {
        return $query->where('email', $email)->first();
    }

    /**
     * Find contact by phone numbers.
     */
    public function findByPhones(array $phones): ?ContactModel
    {
        $numbers = ! isset($phones[0]['number']) ? $phones : array_filter((array) data_get($phones, '*.number'));

        return PhoneModel::where('phoneable_type', ContactModel::class)
            ->whereIn('number', $numbers)
            ->first()?->phoneable;
    }

    /**
     * Get the resource available Filters
     */
    public function filters(ResourceRequest $request): array
    {
        return [
            TextFilter::make('first_name', __('contacts::fields.contacts.first_name'))->withoutNullOperators(),
            TextFilter::make('last_name', __('contacts::fields.contacts.last_name')),
            TextFilter::make('email', __('contacts::fields.contacts.email')),
            UserFilter::make(__('contacts::fields.contacts.user.name'))->inQuickFilter(),
            ResourceUserTeamFilter::make(__('users::team.owner_team')),
            DateTimeFilter::make('owner_assigned_date', __('contacts::fields.contacts.owner_assigned_date')),
            TagsFilter::make('tags', __('core::tags.tags'))->forType(ContactModel::TAGS_TYPE)->inQuickFilter(multiple: true),
            ResourceDocumentsFilter::make(),
            ResourceActivitiesFilter::make(),
            SourceFilter::make(),
            TextFilter::make('job_title', __('contacts::fields.contacts.job_title')),
            AddressOperandFilter::make('contacts'),
            UserFilter::make(__('core::app.created_by'), 'created_by')->withoutNullOperators()->canSeeWhen('view all contacts'),

            HasFilter::make('phones', __('contacts::fields.contacts.phone'))->setOperands([
                Operand::from(TextFilter::make('number', __('contacts::fields.contacts.phone'))),
            ])->hideOperands()->checkRelationExistence(),

            ResourceDealsFilter::make(__('contacts::contact.contact')),
            ResourceEmailsFilter::make(),
            DateTimeFilter::make('last_contacted_at', __('contacts::fields.last_contacted_at')),
            DateTimeFilter::make('next_activity_date', __('activities::activity.next_activity_date')),
            CreatedAtFilter::make()->inQuickFilter(),
            UpdatedAtFilter::make(),

            HasFilter::make('companies', __('contacts::company.company'))->setOperands(
                fn () => Innoclapps::resourceByName('companies')
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
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('contacts::contact.contacts');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('contacts::contact.contact');
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
                        'export contacts' => __('core::app.export.export'),
                    ],
                ]);
            });
        });
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
                Panel::make('contact-detail-panel', 'resource-details-panel')
                    ->heading(__('core::app.record_view.sections.details'))
                    ->resizeable(),
                Panel::make('deals', 'resource-deals-panel')->heading(__('deals::deal.deals')),
                Panel::make('companies', 'resource-companies-panel')->heading(__('contacts::company.companies')),
                Panel::make('media', 'resource-media-panel')->heading(__('core::app.attachments')),
            ];
        });
    }
}
