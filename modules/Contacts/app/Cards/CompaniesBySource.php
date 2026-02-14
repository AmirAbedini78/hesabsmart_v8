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

namespace Modules\Contacts\Cards;

use Illuminate\Http\Request;
use Modules\Contacts\Criteria\ViewAuthorizedCompaniesCriteria;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Source;
use Modules\Core\Charts\Presentation;

class CompaniesBySource extends Presentation
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
    protected $sources;

    /**
     * Calculates companies by source
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $query = Company::criteria(ViewAuthorizedCompaniesCriteria::class);

        return $this->byDays('created_at')->count($request, $query, 'source_id')->label(function ($value) {
            return $this->sources()->find($value)->name ?? 'N\A';
        });
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
     * Get all available sources
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function sources()
    {
        return $this->sources ??= Source::get(['id', 'name']);
    }

    /**
     * The card name
     */
    public function name(): string
    {
        return __('contacts::company.cards.by_source');
    }
}
