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

namespace Modules\Activities\Actions;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Modules\Core\Actions\Action;
use Modules\Core\Actions\ActionFields;
use Modules\Core\Http\Requests\ActionRequest;

class DownloadIcsFile extends Action
{
    /**
     * Indicates that the action does not have confirmation dialog.
     */
    public bool $withoutConfirmation = true;

    /**
     * The XHR response type that should be passed from the front-end.
     */
    public string $responseType = 'blob';

    /**
     * Handle method.
     */
    public function handle(Collection $models, ActionFields $fields): Response
    {
        $activity = $models->first();

        return response($activity->generateICSInstance()->get(), 200, [
            'Content-Type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename='.$activity->icsFilename().'.ics',
            'charset' => 'utf-8',
        ]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        return $request->user()->can('view', $model);
    }

    /**
     * Action name.
     */
    public function name(): string
    {
        return __('activities::activity.download_ics');
    }
}
