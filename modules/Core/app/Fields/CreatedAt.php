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

class CreatedAt extends DateTime
{
    /**
     * Initialize new CreatedAt instance.
     */
    public function __construct(string $attribute = 'created_at', ?string $label = null)
    {
        parent::__construct($attribute, $label ?: __('core::app.created_at'));

        $this->excludeFromImportSample()
            ->excludeFromCreate()
            ->excludeFromUpdate()
            ->readonly(true);
    }
}
