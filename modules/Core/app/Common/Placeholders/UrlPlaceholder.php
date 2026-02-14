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

namespace Modules\Core\Common\Placeholders;

use Modules\Core\Contracts\Resources\Resourceable;

class UrlPlaceholder extends Placeholder
{
    /**
     * Initialize new UrlPlaceholder instance.
     *
     * @param  \Closure|mixed  $value
     */
    public function __construct($value = null, string $tag = 'url')
    {
        parent::__construct($tag, $value);

        $this->description(__('core::app.url'));
    }

    /**
     * Format the placeholder
     *
     * @return string
     */
    public function format(?string $contentType = null)
    {
        /** @var string|(Resourceable&\Illuminate\Database\Eloquent\Model) */
        $value = $this->value;

        return url(
            $value instanceof Resourceable ? $value->resource()->viewRouteFor($value) : $value
        );
    }
}
