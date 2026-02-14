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

namespace Modules\Documents\Cards;

use Illuminate\Http\Request;
use Modules\Core\Charts\Presentation;
use Modules\Documents\Criteria\ViewAuthorizedDocumentsCriteria;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentType;

class DocumentsByType extends Presentation
{
    /**
     * The default renge/period selected
     *
     * @var int
     */
    public string|int|null $defaultRange = 30;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $types;

    /**
     * Calculates companies by source
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $query = Document::criteria(ViewAuthorizedDocumentsCriteria::class);

        return $this->byDays('created_at')
            ->count($request, $query, 'document_type_id')
            ->label(function ($value) {
                return $this->types()->find($value)->name ?? 'N\A';
            })->colors($this->types()->mapWithKeys(function (DocumentType $type) {
                return [$type->name => $type->swatch_color];
            })->all());
    }

    /**
     * Get the ranges available for the chart.
     */
    public function ranges(): array
    {
        return [
            7 => __('core::dates.periods.7_days'),
            15 => __('core::dates.periods.15_days'),
            30 => __('core::dates.periods.30_days'),
            60 => __('core::dates.periods.60_days'),
            90 => __('core::dates.periods.90_days'),
            365 => __('core::dates.periods.365_days'),
        ];
    }

    /**
     * Get all available types
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function types()
    {
        if (! $this->types) {
            $this->types = DocumentType::select(
                ['id', 'name', 'swatch_color']
            )->get();
        }

        return $this->types;
    }

    /**
     * The card name
     */
    public function name(): string
    {
        return __('documents::document.cards.by_type');
    }
}
