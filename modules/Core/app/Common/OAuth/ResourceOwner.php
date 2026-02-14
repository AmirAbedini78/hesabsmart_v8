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

namespace Modules\Core\Common\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class ResourceOwner implements ResourceOwnerInterface
{
    public function __construct(protected array $response) {}

    /**
     * Get the owner identifier
     *
     * @return string
     */
    public function getId()
    {
        return $this->response['id'];
    }

    /**
     * Get the resource owner email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['email'] ?? null;
    }

    /**
     * Returns the raw resource owner response.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
