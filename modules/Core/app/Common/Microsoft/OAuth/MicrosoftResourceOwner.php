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

namespace Modules\Core\Common\Microsoft\OAuth;

use Modules\Core\Common\OAuth\ResourceOwner;

class MicrosoftResourceOwner extends ResourceOwner
{
    /**
     * Get the resource owner email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->response['email'] ?? $this->response['userPrincipalName'];
    }
}
