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

namespace Modules\Core\Table;

class ActionColumn extends Column
{
    public bool $sortable = false;

    protected bool $resizeable = false;

    protected bool $canToggleVisibility = false;

    public string $attribute = 'actions';

    public ?string $label = null;

    public ?string $minWidth = '48px';

    public string $width = '48px';

    public string $align = 'center';

    /**
     * Initialize new ActionColumn instance.
     */
    public function __construct() {}
}
