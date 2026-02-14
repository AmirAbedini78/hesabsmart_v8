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

namespace Modules\Documents\Filters;

use Modules\Core\Filters\MultiSelect;
use Modules\Documents\Enums\DocumentStatus;

class DocumentStatusFilter extends MultiSelect
{
    /**
     * Initialize new DocumentStatusFilter instance.
     */
    public function __construct()
    {
        parent::__construct('status', __('documents::document.status.status'));

        $this->options(collect(DocumentStatus::cases())
            ->map(function (DocumentStatus $status) {
                return [
                    $this->valueKey => $status->value,
                    $this->labelKey => $status->displayName(),
                    'swatch_color' => $status->color(),
                ];
            })->all());
    }

    /**
     * Prepare the query value.
     *
     * @return array<array-key, \Modules\Documents\Enums\DocumentStatus|null>
     */
    public function prepareValue(array $value): array
    {
        return collect($value)->map(
            fn ($status) => DocumentStatus::tryFrom($status)
        )->filter()->all();
    }
}
