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

namespace Modules\Translator\Loaders;

use Modules\Translator\Contracts\TranslationLoader;

class OverrideFileLoader implements TranslationLoader
{
    /**
     * Create new OverrideFileLoader instance
     */
    public function __construct(protected string $overridePath) {}

    /**
     * Get the override path
     */
    public function getOverridePath(): string
    {
        return $this->overridePath;
    }

    /**
     * Returns all translations for the given locale and group.
     */
    public function loadTranslations(string $locale, string $group, ?string $namespace = null): array
    {
        $localePath = $this->overridePath.DIRECTORY_SEPARATOR.$locale.DIRECTORY_SEPARATOR;

        if (! $namespace || $namespace === '*') {
            $groupPath = $localePath.$group.'.php';
        } else {
            $groupPath = $localePath.'_'.$namespace.DIRECTORY_SEPARATOR.$group.'.php';
        }

        if (file_exists($groupPath)) {
            $translations = include $groupPath;
        }

        return $translations ?? [];
    }
}
