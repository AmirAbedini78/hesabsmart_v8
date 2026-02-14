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

namespace Modules\Documents\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Billable\Contracts\BillableResource;
use Modules\Billable\Fields\Amount;
use Modules\Billable\Filters\BillableProductsFilter;
use Modules\Brands\Models\Brand;
use Modules\Contacts\Fields\Companies;
use Modules\Contacts\Fields\Contacts;
use Modules\Core\Actions\Action;
use Modules\Core\Actions\CloneAction;
use Modules\Core\Actions\DeleteAction;
use Modules\Core\Contracts\Resources\Cloneable;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Fields\BelongsTo;
use Modules\Core\Fields\CreatedAt;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\UpdatedAt;
use Modules\Core\Fields\User;
use Modules\Core\Filters\CreatedAt as CreatedAtFilter;
use Modules\Core\Filters\DateTime as DateTimeFilter;
use Modules\Core\Filters\FilterChildGroup;
use Modules\Core\Filters\FilterGroups;
use Modules\Core\Filters\Numeric as NumericFilter;
use Modules\Core\Filters\Text as TextFilter;
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
use Modules\Documents\Concerns\ValidatesDocument;
use Modules\Documents\Criteria\ViewAuthorizedDocumentsCriteria;
use Modules\Documents\Enums\DocumentStatus;
use Modules\Documents\Filters\DocumentBrandFilter;
use Modules\Documents\Filters\DocumentStatusFilter;
use Modules\Documents\Filters\DocumentTypeFilter;
use Modules\Documents\Http\Resources\DocumentResource;
use Modules\Documents\Models\DocumentType;
use Modules\Documents\Services\DocumentService;
use Modules\Users\Filters\ResourceUserTeamFilter;
use Modules\Users\Filters\UserFilter;

class Document extends Resource implements BillableResource, Cloneable, Tableable, WithResourceRoutes
{
    use ValidatesDocument;

    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'title';

    /**
     * Indicates whether the resource is globally searchable
     */
    public static bool $globallySearchable = true;

    /**
     * The resource displayable icon.
     */
    public static ?string $icon = 'DocumentText';

    /**
     * The attribute to be used when the resource should be displayed.
     */
    public static string $title = 'title';

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Documents\Models\Document';

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make(static::label(), '/documents')
                ->icon(static::$icon)
                ->position(20)
                ->inQuickCreate()
                ->keyboardShortcutChar('F')
                ->singularName(static::singularLabel()),
        ];
    }

    /**
     * Create new resource record in storage.
     */
    public function create(Model $model, ResourceRequest $request): Model
    {
        return (new DocumentService)->create($model, $request->all());
    }

    /**
     * Update resource record in storage.
     */
    public function update(Model $model, ResourceRequest $request): Model
    {
        return (new DocumentService)->update($model, $request->all());
    }

    /**
     * Get the resource relationship name when it's associated
     */
    public function associateableName(): string
    {
        return 'documents';
    }

    /**
     * Get the resource available cards
     */
    public function cards(): array
    {
        return [
            (new \Modules\Documents\Cards\SentDocumentsByDay)->withUserSelection(function ($instance) {
                return $instance->authorizedToFilterByUser();
            })->color('success'),
            (new \Modules\Documents\Cards\DocumentsByType)->onlyOnDashboard()->help(__('core::app.cards.creation_date_info')),
            (new \Modules\Documents\Cards\DocumentsByStatus)->refreshOnActionExecuted()->help(__('core::app.cards.creation_date_info')),
        ];
    }

    /**
     * Provide the resource table class instance.
     */
    public function table(Builder $query, ResourceRequest $request, string $identifier): Table
    {
        return Table::make($query, $request, $identifier)
            ->withViews()
            ->withDefaultView(
                name: 'documents::document.views.all',
                flag: 'all-documents',
            )
            ->withDefaultView(
                name: 'documents::document.views.my',
                flag: 'my-documents',
                rules: new FilterGroups(new FilterChildGroup(rules: [
                    UserFilter::make()->setOperator('equal')->setValue('me'),
                ], quick: true))
            )
            ->withActionsColumn()
            ->appends(['public_url'])
            ->orderBy('created_at', 'desc')
            ->select([
                'uuid', // for public_url append
                'user_id', // user_id is for the policy checks
                'status', // for showing the dropdown send document item and disable inline edit checks
            ]);
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return DocumentResource::class;
    }

    /**
     * Prepare global search query.
     */
    public function globalSearchQuery(ResourceRequest $request): Builder
    {
        return parent::globalSearchQuery($request)->select(['id', 'title', 'created_at']);
    }

    /**
     * Get columns that should be used for global search.
     */
    public function globalSearchColumns(): array
    {
        return ['title' => 'like'];
    }

    /**
     * Get the resource search columns.
     */
    public function searchableColumns(): array
    {
        return [
            'title' => 'like',
            'status',
            'amount',
            'brand_id',
            'document_type_id',
            'sent_by',
            'user_id',
            'created_by',
        ];
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): string
    {
        return ViewAuthorizedDocumentsCriteria::class;
    }

    /**
     * Clone the resource record from the given id
     */
    public function clone(Model $model, int $userId): Model
    {
        return $model->clone($userId);
    }

    /**
     * Resolve the fields for placeholders.
     */
    public function fieldsForPlaceholders(): FieldsCollection
    {
        return $this->fieldsForIndex()->filterForPlaceholders();
    }

    /**
     * Resolve the fields for index.
     */
    public function fieldsForIndex(): FieldsCollection
    {
        $indexFields = new FieldsCollection([
            Text::make('title', __('documents::fields.documents.title'))
                ->required()
                ->tapIndexColumn(fn (Column $column) => $column
                    ->width('300px')->minWidth('200px')
                    ->primary()
                    ->route(! $column->isForTrashedTable() ? '/documents/{id}' : '')
                )
                ->disableInlineEdit(fn ($model) => $model->status === DocumentStatus::ACCEPTED),

            BelongsTo::make('type', DocumentType::class, __('documents::document.type.type'))
                ->required()
                ->displayAsBadges()
                ->tapIndexColumn(fn (BelongsToColumn $column) => $column
                    ->width('200px')
                    ->select('swatch_color')
                    ->appends(['swatch_color', 'icon'])
                ),

            // TODO: Not displayed properly on trashed table.
            Text::make('status', __('documents::document.status.status'))
                ->tapIndexColumn(fn (Column $column) => $column
                    ->width('200px')
                    ->centered()
                )
                ->displayUsing(fn ($model, $value) => DocumentStatus::tryFrom($value)->displayName()) // for mail placeholder
                ->resolveUsing(fn ($model) => $model->status->value)
                ->disableInlineEdit(),

            User::make(__('documents::fields.documents.user.name'))
                ->required()
                ->tapIndexColumn(fn (Column $column) => $column->queryWhenHidden()) // policy
                ->disableInlineEdit(fn ($model) => $model->status === DocumentStatus::ACCEPTED),

            Amount::make('amount', __('documents::fields.documents.amount'))
                ->currency()
                ->onlyProducts()
                ->disableInlineEdit(fn ($model) => $model->status === DocumentStatus::ACCEPTED),

            BelongsTo::make('brand', Brand::class, __('brands::brand.brand'))
                ->required()
                ->displayAsBadges()
                ->disableInlineEdit(fn ($model) => $model->status === DocumentStatus::ACCEPTED)
                ->tapIndexColumn(fn (BelongsToColumn $column) => $column
                    ->select('config')
                    ->fillRowDataUsing(function (array &$row, Model $model) use ($column) {
                        $row['brand'] = $column->toRowData($model->brand, [
                            'swatch_color' => $model->brand->config['primary_color'],
                        ]);
                    })
                )->hidden(),

            Contacts::make()->hidden(),

            Companies::make()->hidden(),

            Deals::make()->hidden(),

            User::make(__('core::app.created_by'), 'creator', 'created_by')
                ->disableInlineEdit()
                ->hidden(),

            DateTime::make('last_date_sent', __('documents::fields.documents.last_date_sent'))
                ->disableInlineEdit()
                ->hidden(),

            DateTime::make('original_date_sent', __('documents::fields.documents.original_date_sent'))
                ->disableInlineEdit()
                ->hidden(),

            DateTime::make('accepted_at', __('documents::fields.documents.accepted_at'))
                ->disableInlineEdit()
                ->hidden(),

            DateTime::make('owner_assigned_date', __('documents::fields.documents.owner_assigned_date'))
                ->disableInlineEdit()
                ->hidden(),

            CreatedAt::make()->hidden(),

            UpdatedAt::make()->hidden(),
        ]);

        return $this->resolveFields()
            ->push(...$indexFields)
            ->filterForIndex();
    }

    /**
     * Get the resource available Filters
     */
    public function filters(ResourceRequest $request): array
    {
        return [
            TextFilter::make('title', __('documents::fields.documents.title'))->withoutNullOperators(),
            DocumentTypeFilter::make()->inQuickFilter(multiple: true),
            NumericFilter::make('amount', __('documents::fields.documents.amount')),
            DocumentBrandFilter::make(),
            DocumentStatusFilter::make()->inQuickFilter(multiple: true),
            DateTimeFilter::make('accepted_at', __('documents::fields.documents.accepted_at')),
            UserFilter::make(__('documents::fields.documents.user.name'))->inQuickFilter()->withoutNullOperators(),
            ResourceUserTeamFilter::make(__('users::team.owner_team')),
            DateTimeFilter::make('owner_assigned_date', __('documents::fields.documents.owner_assigned_date')),
            BillableProductsFilter::make(),
            DateTimeFilter::make('last_date_sent', __('documents::fields.documents.last_date_sent')),
            DateTimeFilter::make('original_date_sent', __('documents::fields.documents.original_date_sent')),
            UserFilter::make(__('documents::document.sent_by'), 'sent_by')->canSeeWhen('view all documents'),
            UserFilter::make(__('core::app.created_by'), 'created_by')->withoutNullOperators()->canSeeWhen('view all documents'),
            CreatedAtFilter::make()->inQuickFilter(),
            UpdatedAtFilter::make(),
        ];
    }

    /**
     * Create the query when the resource is associated and the data is intended for the timeline.
     */
    public function timelineQuery(Model $subject, ResourceRequest $request): Builder
    {
        return parent::timelineQuery($subject, $request)->criteria($this->viewAuthorizedRecordsCriteria());
    }

    /**
     * Provides the resource available actions
     */
    public function actions(ResourceRequest $request): array
    {
        return [
            new \Modules\Users\Actions\AssignOwnerAction,

            Action::using('view-document', __('documents::document.view'), function (Collection $models) {
                return Action::openInNewTab($models->first()->public_url);
            })->withoutConfirmation()->sole()->onlyInline(),

            CloneAction::make()->onlyInline(),

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
        return __('documents::document.documents');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('documents::document.document');
    }

    /**
     * Register permissions for the resource
     */
    public function registerPermissions(): void
    {
        $this->registerCommonPermissions();
    }

    /**
     * Register the settings menu items for the resource
     */
    public function settingsMenu(): array
    {
        return [
            SettingsMenuItem::make($this->name(), __('documents::document.documents'))
                ->path('/documents')
                ->icon('DocumentText')
                ->order(23),
        ];
    }
}
