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

namespace Modules\Translator\Contracts;

interface TranslationLoader
{
    /**
     * Returns all translations for the given locale and group.
     */
    public function loadTranslations(string $locale, string $group, string $namespace): array;

    /**
     * Get the override path
     */
    public function getOverridePath(): string;
}
