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

namespace Modules\Core\Settings\Stores;

class ArrayStore extends AbstractStore
{
    /**
     * Fire the post options to customize the store.
     */
    protected function postOptions(array $options)
    {
        // Do nothing...
    }

    /**
     * Read the data from the store.
     */
    protected function read(): array
    {
        return $this->data;
    }

    /**
     * Write the data into the store.
     */
    protected function write(array $data): void
    {
        // Nothing to do...
    }
}
