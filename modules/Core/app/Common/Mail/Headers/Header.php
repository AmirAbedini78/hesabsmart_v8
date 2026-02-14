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

namespace Modules\Core\Common\Mail\Headers;

use Illuminate\Contracts\Support\Arrayable;

class Header implements Arrayable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|array
     */
    protected $value;

    /**
     * Initialize header
     *
     * @param  string  $name
     * @param  string|array  $value
     */
    public function __construct($name, $value)
    {
        $this->name = strtolower(trim($name));
        $this->value = is_string($value) ? trim($value) : $value;
    }

    /**
     * Get the header name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the header value
     *
     * @return string|array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'value' => $this->getValue(),
        ];
    }
}
