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

namespace Modules\Deals\Enums;

use Modules\Core\Support\InteractsWithEnums;

enum DealStatus: int
{
    use InteractsWithEnums;

    case open = 1;
    case won = 2;
    case lost = 3;

    /**
     * Get the status label.
     */
    public function label(): string
    {
        return __('deals::deal.status.'.$this->name);
    }

    /**
     * Get the deal status badge variant.
     */
    public function badgeVariant(): string
    {
        return self::badgeVariants()[$this->name];
    }

    /**
     * Get the available badge variants.
     */
    public static function badgeVariants(): array
    {
        return [
            DealStatus::open->name => 'neutral',
            DealStatus::won->name => 'success',
            DealStatus::lost->name => 'danger',
        ];
    }
}
