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

namespace Modules\MailClient\Synchronization\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SynchronizationInProgressException extends Exception
{
    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct(__('mailclient::inbox.sync_in_progress'), Response::HTTP_CONFLICT);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): Response
    {
        return response(['message' => $this->message], $this->code);
    }

    /**
     * Report the exception.
     */
    public function report(): bool
    {
        return true;
    }
}
