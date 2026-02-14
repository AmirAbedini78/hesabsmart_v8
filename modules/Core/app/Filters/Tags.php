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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Core\Models\Tag;

class Tags extends Optionable
{
    public bool $allowMultipleOptionsInQuickFilter = true;

    /**
     * The type the tags are intended for.
     */
    protected ?string $type = null;

    /**
     * Initialize new Tags instance.
     */
    public function __construct(string $field, ?string $label = null, ?array $operators = null)
    {
        parent::__construct($field, $label, $operators);

        $this->options($this->tags(...));
    }

    /**
     * Apply the filter for the given query.
     */
    public function apply(Builder $query, string $boolean, QueryBuilder $builder): Builder
    {
        return $query->whereHas(
            'tags',
            function (Builder $query) use ($builder, $boolean) {
                $query->withoutGlobalScope('displayOrder');

                $query->when($this->type, fn (Builder $query) => $query->withType($this->type));

                return $builder->applyFilterOperatorQuery($query, $this, $boolean, 'tags.id');
            }
        );
    }

    /**
     * Defines a filter type
     */
    public function type(): string
    {
        return 'multi-select';
    }

    /**
     * Add the type the tags are intended for.
     */
    public function forType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the tags as option for the filter.
     */
    protected function tags(): Collection
    {
        return Tag::query()
            ->when($this->type, function (Builder $query) {
                return $query->withType($this->type);
            })
            ->get()
            ->map(fn (Tag $tag) => [
                $this->valueKey => $tag->id,
                $this->labelKey => $tag->name,
                'swatch_color' => $tag->swatch_color,
            ]);
    }
}
