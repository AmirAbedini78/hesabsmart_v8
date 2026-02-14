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

namespace Modules\Documents\Content;

use Modules\Documents\Concerns\ManualRTLDirection;
use Modules\Documents\Concerns\Utf8Glyphs;

class FixRightToLeftLanguages
{
    use ManualRTLDirection, Utf8Glyphs;

    public function process($html): string
    {
        $html = html_entity_decode($html);

        $html = $this->manualRTLDirection(
            $this->utf8Glyphs($this->replaceYeWithUnicode($html), 10000)
        );

        return $html;
    }

    /**
     * Replaces the Persian letter "ی" (Yeh) with its Arabic Unicode equivalent.
     *
     * This function scans the input text and replaces occurrences of the Persian "ی"
     * that follow any character with the Arabic "ي" (Yeh) using its Unicode representation.
     *
     * @param  string  $text  The input text to process.
     * @return string The text with the Persian "ی" replaced by the Arabic equivalent.
     */
    protected function replaceYeWithUnicode($text): string
    {
        $arabicYe = "\u{064A}";
        $text = preg_replace('/(.)ی/', '$1'.$arabicYe, $text);

        return $text;
    }
}
