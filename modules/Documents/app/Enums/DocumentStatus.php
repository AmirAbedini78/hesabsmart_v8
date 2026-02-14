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

namespace Modules\Documents\Enums;

enum DocumentStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case LOST = 'lost';

    /**
     * Get the status color.
     */
    public function color(): string
    {
        return match ($this) {
            DocumentStatus::DRAFT => '#64748b',
            DocumentStatus::SENT => '#3b82f6',
            DocumentStatus::ACCEPTED => '#22c55e',
            DocumentStatus::LOST => '#f43f5e',
        };
    }

    /**
     * Get the status icon.
     */
    public function icon(): string
    {
        return match ($this) {
            DocumentStatus::DRAFT => 'LightBulb',
            DocumentStatus::SENT => 'Mail',
            DocumentStatus::ACCEPTED => 'Check',
            DocumentStatus::LOST => 'XSolid',
        };
    }

    /**
     * Get the status displayable name.
     */
    public function displayName(): string
    {
        return __('documents::document.status.'.$this->value) ?: $this->value;
    }
}
