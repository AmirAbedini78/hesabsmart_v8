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

namespace Modules\MailClient\Client\Exceptions;

use Exception;
use Throwable;

class ServiceUnavailableException extends Exception
{
    /**
     * The retry after date value returned from the response either
     * via error message or header indicating when the request can be retried.
     */
    protected ?string $retryAfter = null;

    /**
     * Initialize new ServiceUnavailableException instance.
     */
    public function __construct(string $message, ?string $retryAfter = null, ?Throwable $previous = null)
    {
        $this->retryAfter = $retryAfter;

        parent::__construct($message, 0, $previous);
    }

    /**
     * Get the retry after date.
     */
    public function retryAfter(): ?string
    {
        return $this->retryAfter;
    }
}
