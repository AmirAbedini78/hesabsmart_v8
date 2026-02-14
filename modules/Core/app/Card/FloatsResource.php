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

trait FloatsResource
{
    protected ?array $floatingResource = null;

    public function floatResourceInEditMode(string $resourceName): static
    {
        $this->floatingResource = ['resourceName' => $resourceName, 'mode' => 'edit'];

        return $this;
    }

    public function floatResourceInDetailMode(string $resourceName): static
    {
        $this->floatingResource = ['resourceName' => $resourceName, 'mode' => 'detail'];

        return $this;
    }
}
