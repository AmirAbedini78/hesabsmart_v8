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

namespace Modules\Core\Concerns;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Modules\Core\Models\Model;
use Modules\Core\Models\UserSortedModel;
use Modules\Users\Models\User;

/** @mixin \Modules\Core\Models\Model */
trait UserSortable
{
    /**
     * Boot the trait.
     */
    protected static function bootUserSortable(): void
    {
        static::deleting(function (Model $model) {
            if ($model->isReallyDeleting()) {
                $model->userSortedModels()->delete();
            }
        });
    }

    /**
     * Save the display order for the model for the given user.
     */
    public function saveUserSortOrder(User|int $user, int $displayOrder): static
    {
        $userId = is_int($user) ? $user : $user->id;
        $instance = $this->userSortedModels()->where('user_id', $userId)->first();

        if (! is_null($instance)) {
            $instance->update(['display_order' => $displayOrder]);
        } else {
            $this->userSortedModels()->create([
                'display_order' => $displayOrder,
                'user_id' => $userId,
            ]);
        }

        return $this;
    }

    /**
     * Get the model user sorted order instances.
     */
    public function userSortedModels(): MorphMany
    {
        return $this->morphMany(UserSortedModel::class, 'sortable');
    }

    /**
     * Get the model current user sorted model.
     */
    public function currentUserSortedModel(): MorphOne
    {
        return $this->morphOne(UserSortedModel::class, 'sortable')->where('user_id', auth()->id());
    }

    /**
     * Apply a scope query to order the records as the user specified.
     */
    public function scopeOrderByUserSpecified(Builder $query, User|int $user): void
    {
        $userId = is_int($user) ? $user : $user->id;

        $table = (new UserSortedModel)->getTable();

        $query->select($this->prepareColumnsForUserOrderedQuery($query))
            ->leftJoin($table, function ($join) use ($userId, $query, $table) {
                $sortable = $query->getModel();

                $join->on($table.'.sortable_id', '=', $sortable->getTable().'.'.$sortable->getKeyName())
                    ->where($table.'.sortable_type', $sortable::class)
                    ->where($table.'.user_id', $userId);
            })
            ->orderBy($table.'.display_order', 'asc');
    }

    /**
     * Qualify the columns to avoid ambigious columns when joining.
     */
    protected function prepareColumnsForUserOrderedQuery(Builder $builder): array|string
    {
        $columns = $builder->getQuery()->columns;

        if (is_null($columns)) {
            return $builder->getModel()->getTable().'.*';
        }

        return collect($columns)->map(function ($column) use ($builder) {
            if (! Str::endsWith($column, '.*') && ! $column instanceof Expression) {
                return $builder->getModel()->qualifyColumn($column);
            }

            return $column;
        })->all();
    }
}
