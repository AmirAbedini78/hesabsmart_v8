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

class PrivacyPolicyPlaceholder extends UrlPlaceholder
{
    /**
     * Initialize new PrivacyPolicyPlaceholder instance.
     */
    public function __construct(string $tag = 'privacy_policy')
    {
        parent::__construct(null, $tag);

        $this->description(__('core::app.privacy_policy'));
    }

    /**
     * Format the placeholder
     *
     * @return string
     */
    public function format(?string $contentType = null)
    {
        return privacy_url();
    }
}
