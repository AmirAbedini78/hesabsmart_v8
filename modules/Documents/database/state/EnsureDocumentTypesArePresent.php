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

namespace Modules\Documents\Database\State;

use Modules\Documents\Models\DocumentType;

class EnsureDocumentTypesArePresent
{
    public array $types = [
        'Proposal' => '#a3e635',
        'Quote' => '#64748b',
        'Contract' => '#ffd600',
    ];

    public function __invoke(): void
    {
        if ($this->present()) {
            return;
        }

        foreach ($this->types as $name => $color) {
            $model = DocumentType::create([
                'name' => $name,
                'swatch_color' => $color,
                'flag' => strtolower($name),
            ]);

            if ($model->flag === 'proposal') {
                $model::setDefault($model->getKey());
            }
        }
    }

    private function present(): bool
    {
        return DocumentType::query()->count() > 0;
    }
}
