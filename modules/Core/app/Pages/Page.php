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

namespace Modules\Core\Pages;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Modules\Core\Support\Makeable;

abstract class Page implements Arrayable, JsonSerializable
{
    use Makeable;

    public function __construct(protected string $id) {}
}
