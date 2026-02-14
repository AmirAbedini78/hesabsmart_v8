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

use Tests\TestCase;

class GenerateJsonLanguageFileCommandTest extends TestCase
{
    public function test_it_generates_json_language_file(): void
    {
        $this->artisan('translator:json')
            ->assertSuccessful()
            ->expectsOutput('Language file generated successfully.');
    }
}
