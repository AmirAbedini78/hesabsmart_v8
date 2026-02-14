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

namespace Modules\Core\Support;

abstract class Element
{
    use Authorizeable,
        Makeable;

    /**
     * Additional element meta.
     */
    public array $meta = [];

    /**
     * Get the element meta.
     */
    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * Add element meta.
     */
    public function withMeta(array $attributes): static
    {
        $this->meta = array_merge_recursive($this->meta, $attributes);

        return $this;
    }
}
