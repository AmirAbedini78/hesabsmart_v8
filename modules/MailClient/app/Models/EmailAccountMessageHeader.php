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

namespace Modules\MailClient\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\Core\Models\Model;

class EmailAccountMessageHeader extends Model
{
    /**
     * Indicates if the model has timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'value', 'header_type'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'message_id' => 'int',
    ];

    /**
     * Get the mapped attribute
     *
     * We will map the header into a appropriate header class
     */
    protected function mapped(): Attribute
    {
        return Attribute::get(
            fn () => new $this->header_type($this->name, $this->value)
        );
    }
}
