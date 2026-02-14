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

use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class TranslationGroup
{
    public function __construct(protected SplFileInfo $file) {}

    public function translations(string $locale, ?string $namespace = null)
    {
        $key = $namespace ? ($namespace.'::'.$this->name()) : $this->name();

        // Use fallback to merge any non existent keys
        $fallback = trans($key, [], trans()->getFallback());

        if ($fallback === $key) {
            $fallback = [];
        }

        // We will be using Laravel trans helper because if the group does not exists
        // Laravel automatically fallback to the fallback locale, in this case, en
        return array_replace_recursive($fallback, trans($key, [], $locale));
    }

    public function name(): string
    {
        return $this->file->getFilenameWithoutExtension();
    }

    public function sourceTranslations()
    {
        return require $this->fullPath();
    }

    public function filename(): string
    {
        return $this->file->getFilename();
    }

    public function fullPath(): string
    {
        return $this->file->getRealPath();
    }

    public function getPath(): string
    {
        return $this->file->getPath();
    }

    public function locale(): string
    {
        return Str::afterLast($this->getPath(), DIRECTORY_SEPARATOR);
    }
}
