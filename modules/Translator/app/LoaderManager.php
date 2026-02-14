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

namespace Modules\Translator;

use Illuminate\Translation\FileLoader;
use Modules\Translator\Contracts\TranslationLoader;

class LoaderManager extends FileLoader
{
    /**
     * Load the messages for the given locale.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     */
    public function load($locale, $group, $namespace = null): array
    {
        $original = parent::load($locale, $group, $namespace);

        // JSON translations are not supported
        if ($group === '*') {
            return $original;
        }

        return array_replace_recursive(
            $original,
            app(TranslationLoader::class)->loadTranslations($locale, $group, $namespace)
        );
    }
}
