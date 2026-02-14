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

use Illuminate\Support\Carbon;
use Tests\TestCase;

class GenerateJsonLanguageFileToolTest extends TestCase
{
    public function test_json_language_tool_can_be_executed(): void
    {
        $this->signIn();

        $this->postJson('/api/tools/json-language')->assertNoContent();

        $this->assertLessThanOrEqual(2, Carbon::parse(filemtime(config('translator.json')))->diffInSeconds());
    }
}
