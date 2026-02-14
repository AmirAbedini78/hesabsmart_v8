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

trait ChecksForDuplicates
{
    /**
     * Add duplicates checker data
     */
    public function checkPossibleDuplicatesWith(string $url, array $params, string $langKey): static
    {
        $this->withMeta([
            'checkDuplicatesWith' => [
                'url' => $url,
                'params' => $params,
                'lang_keypath' => $langKey,
            ],
        ]);

        return $this;
    }

    /**
     * Disable the duplicate checks for the field.
     */
    public function disableDuplicateChecks(): static
    {
        unset($this->meta['checkDuplicatesWith']);

        return $this;
    }
}
