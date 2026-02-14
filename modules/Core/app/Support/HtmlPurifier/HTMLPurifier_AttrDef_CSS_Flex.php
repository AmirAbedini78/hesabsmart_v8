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

namespace Modules\Core\Support\HtmlPurifier;

use HTMLPurifier_AttrDef;

class HTMLPurifier_AttrDef_CSS_Flex extends HTMLPurifier_AttrDef
{
    /**
     * @param  string  $string
     * @param  HTMLPurifier_Config  $config
     * @param  HTMLPurifier_Context  $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        $string = $this->parseCDATA($string);

        if ($string === '') {
            return false;
        }

        return clean($string);
    }
}
