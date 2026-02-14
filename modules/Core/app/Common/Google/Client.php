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

namespace Modules\Core\Common\Google;

use Google\Client as GoogleClient;
use Modules\Core\Common\Google\Services\Calendar;
use Modules\Core\Common\Google\Services\History;
use Modules\Core\Common\Google\Services\Labels;
use Modules\Core\Common\Google\Services\Message;
use Modules\Core\Common\OAuth\AccessTokenProvider;
use Modules\Core\Common\OAuth\OAuthManager;

class Client
{
    /**
     * Google Client instance.
     */
    protected ?GoogleClient $client = null;

    /**
     * The OAuth email address to use to fetch the token.
     */
    protected static ?string $email = null;

    /**
     * Provide a connector for the access token.
     */
    public function connectUsing(string|AccessTokenProvider $connector): static
    {
        static::$email = is_string($connector) ? $connector : $connector->getEmail();

        // Reset the client so the next time can be retrieved with the new connector
        $this->client = null;

        return $this;
    }

    /**
     * Create new Labels instance.
     */
    public function labels(): Labels
    {
        return new Labels($this->getClient());
    }

    /**
     * Create new Message instance.
     */
    public function message(): Message
    {
        return new Message($this->getClient());
    }

    /**
     * Create new History instance.
     */
    public function history(): History
    {
        return new History($this->getClient());
    }

    /**
     * Create new Calendar instance.
     */
    public function calendar(): Calendar
    {
        return new Calendar($this->getClient());
    }

    /**
     * Get the Google client instance.
     */
    public function getClient(): GoogleClient
    {
        if ($this->client) {
            return $this->client;
        }

        $client = new GoogleClient;

        // Perhaps via revoke?
        if ($email = static::$email) {
            $client->setAccessToken([
                'access_token' => (new OAuthManager)->retrieveAccessToken('google', $email),
            ]);
        }

        return $this->client = $client;
    }

    /**
     * Revoke the current token.
     *
     * The access token to revoke or the current one that is set via the connectUsing method will be used.
     */
    public function revokeToken(?string $accessToken = null): void
    {
        $this->getClient()->revokeToken($accessToken);
    }
}
