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

use Exception;
use Modules\Core\Contracts\Resources\Resourceable;

class GenericPlaceholder extends Placeholder
{
    /**
     * Format the placeholder
     *
     * @return string
     */
    public function format(?string $contentType = null)
    {
        /** @var string|(Resourceable&\Illuminate\Database\Eloquent\Model) */
        $value = $this->value;

        if ($value instanceof Resourceable) {
            return $value->resource()->titleFor($value);
        }

        return $value;
    }

    /**
     * Serialize the placeholder for the front end
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        if (! $data['tag']) {
            throw new Exception('"tag" not provided for generic placeholder.');
        }

        return $data;
    }
}
