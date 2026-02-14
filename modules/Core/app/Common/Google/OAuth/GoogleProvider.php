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

namespace Modules\Core\Common\Google\OAuth;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;

class GoogleProvider extends Google
{
    /**
     * Generate a user object from a successful user details request.
     */
    protected function createResourceOwner(array $response, AccessToken $token): GoogleUser
    {
        return new GoogleResourceOwner($response);
    }

    // https://github.com/thephpleague/oauth2-client/issues/1052
    // https://github.com/thephpleague/oauth2-client/pull/1053
    public function getAccessToken($grant, array $options = [])
    {
        if (empty($options['scope'])) {
            // We use the original scopes
            $options['scope'] = $this->scopes;
        }

        return parent::getAccessToken($grant, $options);
    }
}
