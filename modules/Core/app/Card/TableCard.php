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

namespace Modules\Core\Card;

use DateTimeInterface;
use Illuminate\Http\Request;

abstract class TableCard extends Card
{
    use FloatsResource;

    /**
     * The primary key for the table row
     */
    protected string $primaryKey = 'id';

    /**
     * Define the card component used on front end
     */
    public function component(): string
    {
        return 'card-table';
    }

    /**
     * Get the card value.
     */
    public function value(Request $request): iterable
    {
        return $this->items($request);
    }

    /**
     * Provide the table fields.
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * Provide the table items.
     */
    public function items(Request $request): iterable
    {
        return [];
    }

    /**
     * Table empty text.
     */
    public function emptyText(): ?string
    {
        return null;
    }

    /**
     * Determine for how many minutes the card value should be cached.
     */
    public function cacheFor(): DateTimeInterface
    {
        return now()->addMinutes(5);
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'fields' => $this->fields(),
            'emptyText' => $this->emptyText(),
            'primaryKey' => $this->primaryKey,
            'floatingResource' => $this->floatingResource,
        ]);
    }
}
