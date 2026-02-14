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

namespace Modules\Translator\Tests\Feature;

use Modules\Translator\TranslationGroup;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;

class TranslationGroupTest extends TestCase
{
    public function test_group_has_filename(): void
    {
        $group = new TranslationGroup($this->newGroupFile());

        $this->assertSame($group->filename(), 'validation.php');
    }

    public function test_group_has_fullpath(): void
    {
        $group = new TranslationGroup($this->newGroupFile());

        $this->assertSame($group->fullPath(), lang_path('en'.DIRECTORY_SEPARATOR.'validation.php'));
    }

    public function test_group_has_path(): void
    {
        $group = new TranslationGroup($this->newGroupFile());

        $this->assertSame($group->getPath(), lang_path('en'));
    }

    public function test_group_has_locale(): void
    {
        $group = new TranslationGroup($this->newGroupFile());

        $this->assertSame($group->locale(), 'en');
    }

    protected function newGroupFile()
    {
        return new SplFileInfo(lang_path('en/validation.php'), 'lang/en', 'lang/en/validation.php');
    }
}
