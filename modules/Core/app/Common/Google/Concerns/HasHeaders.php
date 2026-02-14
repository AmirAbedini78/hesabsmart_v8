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

namespace Modules\Core\Common\Google\Concerns;

trait HasHeaders
{
    /**
     * @var \Modules\Core\Common\Mail\Headers\HeadersCollection
     */
    protected $headers;

    /**
     * Get all headers for the configured part
     *
     * @return \Modules\Core\Common\Mail\Headers\HeadersCollection
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get single header value
     *
     * @return \Modules\Core\Common\Mail\Headers\Header|null
     */
    public function getHeader($name)
    {
        return $this->headers->find($name);
    }

    /**
     * Get single header value
     *
     * @return string|null
     */
    public function getHeaderValue($name)
    {
        $header = $this->getHeader($name);

        return $header ? $header->getValue() : null;
    }
}
