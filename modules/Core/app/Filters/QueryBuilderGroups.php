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

namespace Modules\Core\Filters;

use Illuminate\Support\Collection;

class QueryBuilderGroups
{
    protected ?array $mappedGroups = null;

    /**
     * Initialize new Groups instance.
     *
     * @param  array  $groups  The groups and conditions applied to the query builder.
     * @param  Collection<object, Filter|OperandFilter>  $filters  The available rules.
     */
    public function __construct(protected array $groups, protected Collection $filters) {}

    /**
     * Get all the groups.
     *
     * @return array<QueryBuilderChildGroup>
     */
    public function all(): array
    {
        $groups = $this->groups;

        // Allow directly providing one child group.
        if (isset($groups['condition'])) {
            $groups = [$groups];
        }

        return $this->mappedGroups ??= collect($groups)->map(
            fn (array $group) => new QueryBuilderChildGroup($group, $this->filters)
        )->all();
    }

    /**
     * Set the groups to be used.
     */
    public function set(array $groups): static
    {
        $this->groups = $groups;
        $this->mappedGroups = null;

        return $this;
    }

    /**
     * Check whether there are any rules in the groups.
     */
    public function hasRules(): bool
    {
        return collect($this->all())->some(fn (QueryBuilderChildGroup $group) => $group->hasRules());
    }

    /**
     * Get all of the available rules.
     *
     * @return Collection<object, Filter|OperandFilter>
     */
    public function filters(): Collection
    {
        return $this->filters;
    }
}
