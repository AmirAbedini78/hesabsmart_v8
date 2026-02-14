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

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\File;
use Modules\Core\Facades\MailableTemplates;
use Modules\Core\Models\MailableTemplate as MailableTemplateModel;
use Modules\Translator\Translator;
use Tests\Fixtures\SampleMailTemplate;
use Tests\TestCase;

class MailableTemplateTest extends TestCase
{
    public function test_mailable_template_is_seeded_when_new_mailable_exist(): void
    {
        MailableTemplates::register(SampleMailTemplate::class)->seed();

        $this->assertDatabaseHas('mailable_templates', [
            'name' => SampleMailTemplate::name(),
            'subject' => SampleMailTemplate::defaultSubject(),
            'html_template' => SampleMailTemplate::defaultHtmlTemplate(),
            'text_template' => SampleMailTemplate::defaultTextMessage(),
            'mailable' => SampleMailTemplate::class,
            'locale' => 'en',
        ]);
    }

    public function test_mailable_templates_are_seeded_when_new_locale_exist(): void
    {
        $translator = new Translator;
        $translator->createLocale('en_TEST');

        $availableTemplates = MailableTemplates::get();

        $total = count($availableTemplates);

        MailableTemplates::seed();

        $this->assertCount($total, MailableTemplateModel::forLocale('en_TEST')->get());

        $this->assertDatabaseHas('mailable_templates', [
            'locale' => 'en_TEST',
            'mailable' => $availableTemplates[0],
        ]);
    }

    public function test_mailable_templates_are_seeded_for_all_locales(): void
    {
        $translator = new Translator;

        $translator->createLocale('en_TEST');
        $translator->createLocale('fr_TEST');

        $availableTemplates = MailableTemplates::get();

        $expected = count($availableTemplates);

        MailableTemplates::seed();

        $this->assertCount($expected, MailableTemplateModel::forLocale('en_TEST')->get());
        $this->assertCount($expected, MailableTemplateModel::forLocale('fr_TEST')->get());

        $this->assertDatabaseHas('mailable_templates', [
            'locale' => 'en_TEST',
            'mailable' => $availableTemplates[0],
        ]);

        $this->assertDatabaseHas('mailable_templates', [
            'locale' => 'fr_TEST',
            'mailable' => $availableTemplates[0],
        ]);
    }

    protected function tearDown(): void
    {
        foreach (['en_TEST', 'fr_TEST'] as $locale) {
            $path = lang_path($locale);

            if (is_dir($path)) {
                File::deepCleanDirectory($path, false);
            }
        }

        parent::tearDown();
    }
}
