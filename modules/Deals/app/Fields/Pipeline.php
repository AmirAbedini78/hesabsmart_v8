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

namespace Modules\Deals\Fields;

use Illuminate\Support\Facades\Auth;
use Modules\Core\Fields\BelongsTo;
use Modules\Core\Rules\VisibleModelRule;
use Modules\Deals\Http\Resources\PipelineResource;
use Modules\Deals\Models\Pipeline as PipelineModel;

class Pipeline extends BelongsTo
{
    /**
     * Creat new Pipeline instance field
     *
     * @param  string  $label  Custom label
     */
    public function __construct($label = null)
    {
        parent::__construct('pipeline', PipelineModel::class, $label ?: __('deals::fields.deals.pipeline.name'));

        $this->setJsonResource(PipelineResource::class)
            ->rules(
                (new VisibleModelRule(new PipelineModel))
                    ->ignore(
                        fn () => with($this->resolveRequest(), function ($request) {
                            return $request->isUpdateRequest() ? $request->record()->pipeline : null;
                        })
                    )
            )
            ->emitChangeEvent()
            ->withDefaultValue(function () {
                return PipelineModel::withCommon()
                    ->with('stages')
                    ->visible()
                    ->orderByUserSpecified(Auth::user())
                    ->first();
            })
            ->acceptLabelAsValue()
            ->withoutClearAction();
    }

    /**
     * Provides the BelongsTo instance options
     */
    public function resolveOptions(): array
    {
        return PipelineModel::select(['id', 'name'])
            ->with('stages')
            ->visible()
            ->orderByUserSpecified(Auth::user())
            ->get()
            ->all();
    }
}
