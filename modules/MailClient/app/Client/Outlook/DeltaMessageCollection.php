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

namespace Modules\MailClient\Client\Outlook;

use Illuminate\Support\Collection;

class DeltaMessageCollection extends Collection
{
    protected static ?string $deltaLink;

    public function setDeltaLink(?string $link): static
    {
        static::$deltaLink = $link;

        return $this;
    }

    public static function getDeltaLink(): ?string
    {
        return static::$deltaLink;
    }
}
