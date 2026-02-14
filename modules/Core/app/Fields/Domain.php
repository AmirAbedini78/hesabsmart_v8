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

namespace Modules\Core\Fields;

use Illuminate\Support\Str;
use Modules\Core\Http\Requests\ResourceRequest;

class Domain extends Field
{
    /**
     * Field component.
     */
    protected static $component = 'domain-field';

    /**
     * Initialize new Domain instance.
     */
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->provideSampleValueUsing(fn () => 'example.com');
    }

    /**
     * Get the field value for the given request
     */
    public function attributeFromRequest(ResourceRequest $request, string $requestAttribute): mixed
    {
        $value = parent::attributeFromRequest($request, $requestAttribute);

        if (is_null($value)) {
            return $value;
        }

        if (! Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return parse_url($value, PHP_URL_HOST);
    }
}
