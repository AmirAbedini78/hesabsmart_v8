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

use Illuminate\Support\Facades\File;
use Modules\Translator\LoaderManager;
use Modules\Translator\Translator;
use Tests\TestCase;

class LoaderManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        foreach (['en_TEST', '.custom/en_TEST', '.custom'] as $folder) {
            $path = lang_path($folder);

            if (is_dir($path)) {
                File::deepCleanDirectory($path, false);
            }
        }

        parent::tearDown();
    }

    public function test_it_uses_the_loader_manager(): void
    {
        $this->assertInstanceOf(LoaderManager::class, app('translation.loader'));
    }

    public function test_it_can_loader_locale_translation_group(): void
    {
        (new Translator)->createLocale('en_TEST');
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $manager = app('translation.loader');
        $groupsTranslations = $manager->load('en_TEST', 'locale_group');

        $this->assertIsArray($groupsTranslations);
        $this->assertCount(2, $groupsTranslations);
        $this->assertArrayHasKey('key', $groupsTranslations);
        $this->assertArrayHasKey('deep', $groupsTranslations);
    }

    public function test_it_merges_the_custom_translations(): void
    {
        $translator = new Translator;
        $translator->createLocale('en_TEST');

        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $translator->save('en_TEST', 'locale_group', [
            'key' => 'changed',
            'deep' => [
                'key' => 'changed',
            ],
            'new' => 'value',
        ]);

        $manager = app('translation.loader');
        $groupsTranslations = $manager->load('en_TEST', 'locale_group');

        $this->assertIsArray($groupsTranslations);
        $this->assertCount(3, $groupsTranslations);
        $this->assertArrayHasKey('key', $groupsTranslations);
        $this->assertArrayHasKey('deep', $groupsTranslations);
        $this->assertArrayHasKey('new', $groupsTranslations);
        $this->assertSame('changed', $groupsTranslations['key']);
        $this->assertSame('changed', $groupsTranslations['deep']['key']);
        $this->assertSame('value', $groupsTranslations['new']);
    }
}
