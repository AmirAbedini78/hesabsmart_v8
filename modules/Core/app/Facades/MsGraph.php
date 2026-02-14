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

namespace Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Core\Common\Microsoft\Client;

/**
 * @method static static connectUsing(string|\Modules\Core\Common\OAuth\AccessTokenProvider)
 * @method static \Microsoft\Graph\Http\GraphRequest createGetRequest(string $endpoint)
 * @method static \Microsoft\Graph\Http\GraphRequest createPostRequest(string $endpoint, null|string $body)
 * @method static \Microsoft\Graph\Http\GraphRequest createPutRequest(string $endpoint, null|string $body)
 * @method static \Microsoft\Graph\Http\GraphRequest createPatchRequest(string $endpoint, null|string $body)
 * @method static \Microsoft\Graph\Http\GraphRequest createDeleteRequest(string $endpoint)
 * @method static \Microsoft\Graph\Http\GraphCollectionRequest createCollectionGetRequest(string $endpoint)
 * @method static \Modules\Core\Common\Microsoft\Services\Batch\Request createBatchRequest(\Modules\Core\Common\Microsoft\Services\Batch\BatchRequests $requests)
 * @method static array iterateCollectionRequest(\Microsoft\Graph\Http\GraphCollectionRequest $collection)
 * @method static string getApiVersion()
 * @method static string setApiVersion(string $version)
 *
 * @see \Modules\Core\Common\Microsoft\Client
 */
class MsGraph extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
