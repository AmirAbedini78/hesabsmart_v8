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
use Illuminate\Http\Request;
use Modules\Core\Concerns\UserSortable;
use Modules\Core\Contracts\Fields\Customfieldable;
use Modules\Core\Criteria\ExportRequestCriteria;
use Modules\Core\Criteria\RequestCriteria;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\BelongsTo;
use Modules\Core\Fields\Field;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Fields\RelationshipCount;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Models\PinnedTimelineSubject;

/**
 * @mixin \Modules\Core\Resource\Resource
 */
trait QueriesResources
{
    /**
     * Get a new query builder for the resource's model table.
     */
    public function newQuery(): Builder
    {
        return $this->newModel()->newQuery();
    }

    /**
     * Get a new query builder for the resource's model table that includes trashed records.
     */
    public function newQueryWithTrashed(): Builder
    {
        return $this->newModel()->withTrashed();
    }

    /**
     * Prepare display query.
     */
    public function displayQuery(): Builder
    {
        $query = $this->applyWith($this->newQuery(), $this->resolveFields());

        if (count($this->associateableRelations()) > 0) {
            $query->withCountAssociations();
        }

        return $query;
    }

    /**
     * Prepare index query.
     */
    public function indexQuery(ResourceRequest $request): Builder
    {
        $query = $this->newQueryWithAuthorizedRecordsCriteria();

        if ($request->missing(RequestCriteria::ORDER_KEY)) {
            $this->applyDefaultOrder($query, $request);
        }

        $query->criteria([
            $this->createFiltersCriteria($request),
            $this->createRequestCriteria($request),
        ]);

        return $this->applyWith($query, $this->fieldsForIndexQuery());
    }

    /**
     * Prepare global search query.
     */
    public function globalSearchQuery(ResourceRequest $request): Builder
    {
        $query = $this->newQueryWithAuthorizedRecordsCriteria();

        $query->criteria($this->createRequestCriteria($request, $this->globalSearchColumns()));

        if ($request->missing(RequestCriteria::ORDER_KEY)) {
            $this->applyDefaultOrder($query, $request);
        }

        return $this->applyDefaultOrder($query, $request);
    }

    /**
     * Prepare search query.
     */
    public function searchQuery(ResourceRequest $request): Builder
    {
        $query = $this->newQueryWithAuthorizedRecordsCriteria();

        $query->criteria($this->createRequestCriteria($request));

        if ($request->missing(RequestCriteria::ORDER_KEY)) {
            $this->applyDefaultOrder($query, $request);
        }

        return $this->applyWith($query, $this->resolveFields());
    }

    /**
     * Create new trashed query instance.
     */
    public function newTrashedQuery(): Builder
    {
        return $this->newQuery()->onlyTrashed();
    }

    /**
     * Prepare trashed index query.
     */
    public function trashedIndexQuery(ResourceRequest $request): Builder
    {
        return $this->indexQuery($request)->onlyTrashed();
    }

    /**
     * Prepare trashed display query.
     */
    public function trashedDisplayQuery(): Builder
    {
        return $this->displayQuery()->onlyTrashed();
    }

    /**
     * Prepare search query for trashed records.
     */
    public function trashedSearchQuery(ResourceRequest $request): Builder
    {
        return $this->searchQuery($request)->onlyTrashed();
    }

    /**
     * Prepare an export query.
     */
    public function exportQuery(ResourceRequest $request, ?FieldsCollection $fields = null): Builder
    {
        $query = $this->newQueryWithAuthorizedRecordsCriteria();

        if (! $request->filters) {
            $query->criteria(new ExportRequestCriteria(
                $request->input('period'),
                $request->input('date_range_field')
            ));
        } else {
            $query->criteria($this->createFiltersCriteria($request));
        }

        if ($request->missing(RequestCriteria::ORDER_KEY)) {
            $this->applyDefaultOrder($query, $request);
        }

        return $this->applyWith($query, $fields ?? $this->fieldsForExport());
    }

    /**
     * Prepare table query.
     */
    public function tableQuery(ResourceRequest $request): Builder
    {
        return $this->newQueryWithAuthorizedRecordsCriteria();
    }

    /**
     * Create new query with the authorized records criteria.
     */
    public function newQueryWithAuthorizedRecordsCriteria(): Builder
    {
        $query = $this->newQuery();

        if ($criteria = $this->viewAuthorizedRecordsCriteria()) {
            $query->criteria($criteria);
        }

        return $query;
    }

    /**
     * Create the query when the resource is associated and the data is intended for the timeline.
     */
    public function timelineQuery(Model $subject, ResourceRequest $request): Builder
    {
        $relation = Innoclapps::resourceByModel($subject)->associateableName();

        $query = $this->newQuery()
            ->select($this->newModel()->prefixColumns())
            ->criteria($this->createRequestCriteria($request))
            ->whereHas($relation, function ($query) use ($subject) {
                return $query->where($subject->getKeyName(), $subject->getKey());
            })
            ->with('pinnedTimelineSubjects')
            ->withTimelinePins($subject)
            ->orderBy((new PinnedTimelineSubject)->getQualifiedCreatedAtColumn(), 'desc');

        if ($query->getModel()->usesTimestamps()) {
            $query->orderBy($query->getModel()->getQualifiedCreatedAtColumn(), 'desc');
        }

        return $this->applyWith($query)->withCountAssociations();
    }

    /**
     * Apply the default order from the resource to the given query.
     */
    public function applyDefaultOrder(Builder $query, Request $request): Builder
    {
        if (in_array(UserSortable::class, class_uses_recursive(static::$model))) {
            return $query->orderByUserSpecified($request->user());
        } else {
            return $query->orderBy(static::$orderBy, static::$orderByDir);
        }
    }

    /**
     * Add "with" relations to the given query.
     */
    public function with(Builder $query): Builder
    {
        return $query->withCommon();
    }

    /**
     * Add "with count" relations to the given query.
     */
    public function withCount(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Add "with" relations to the given query from the given fields.
     */
    public function withViaFields(Builder $query, $fields): Builder
    {
        $fields = $fields->withoutZapierExcluded();

        $relations = $fields->pluck('with')->flatten()
            ->merge($fields->whereInstanceOf(BelongsTo::class)->pluck('belongsToRelation'))
            ->merge($fields->filterCustomFields()->filter(function (Field&Customfieldable $field) {
                return $field->isOptionable();
            })->pluck('customField.relationName'))
            ->filter()
            ->unique();

        return $query->with($relations->all());
    }

    /**
     * Add "with count" relations to the given query from the given fields.
     */
    public function withCountViaFields(Builder $query, $fields)
    {
        return $query->withCount(
            $fields->whereInstanceOf(RelationshipCount::class)
                ->pluck('countRelation')
                ->filter()
                ->unique()
                ->all()
        );
    }

    /**
     * Apply the resource eager loaded relations and counts for the given query and/or fields.
     */
    protected function applyWith(Builder $query, $fields = null): Builder
    {
        if ($fields) {
            $this->withViaFields($query, $fields);
            $this->withCountViaFields($query, $fields);
        }

        $this->with($query);
        $this->withCount($query);

        return $query;
    }

    /**
     * Get the fields when creating index query
     */
    protected function fieldsForIndexQuery(): FieldsCollection
    {
        return $this->resolveFields()->reject(fn (Field $field) => $field->excludeFromIndexQuery);
    }
}
