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

namespace Modules\ThemeStyle\Http\Controllers;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ThemeStyle extends Controller
{
    /**
     * Output the theme style custom CSS.
     */
    public function __invoke(): Response
    {
        /** Using past timestamp if no theme style applied */
        $modifiedTimestamp = (string) (settings('theme_style_modified_at') ?? '1682202438');
        $modifiedAt = DateTime::createFromFormat('U', $modifiedTimestamp);

        return response(
            $content = $this->parseCss(settings('theme_style')),
            empty($content) ? 204 : 200,
            [
                'Content-Type' => 'text/css',
            ]
        )->setLastModified($modifiedAt);
    }

    /**
     * Parse the theme style CSS.
     */
    protected function parseCss(?string $style): string
    {
        if (! $style || ! Str::isJson($style)) {
            return '';
        }

        $style = json_decode($style, true);

        $css = ':root {';

        foreach ($style as $color => $options) {
            foreach ($options['swatches'] as $swatch) {
                [$r, $g, $b] = sscanf($swatch['hex'], '#%02x%02x%02x');
                $css .= "--color-$color-{$swatch['stop']}: $r, $g, $b !important;";
            }
        }

        $css .= '}';

        return $css;
    }
}
