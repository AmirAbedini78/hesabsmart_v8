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

namespace Modules\Core\Tests\Feature\Macros;

use Illuminate\Support\Str;
use Tests\TestCase;

class ClickableUrlsTest extends TestCase
{
    public function test_it_makes_urls_clickable(): void
    {
        $formatted = Str::clickable('Test https://concordcrm.com Test');

        $this->assertStringContainsString('<a href="https://concordcrm.com" rel="nofollow" target=\'_blank\'>https://concordcrm.com</a>', $formatted);
    }

    public function test_it_makes_multiple_urls_clickable(): void
    {
        $formatted = Str::clickable('Test https://concordcrm.com Test http://concordcrm.com');

        $this->assertStringContainsString('<a href="https://concordcrm.com" rel="nofollow" target=\'_blank\'>https://concordcrm.com</a>', $formatted);
        $this->assertStringContainsString('<a href="http://concordcrm.com" rel="nofollow" target=\'_blank\'>http://concordcrm.com</a>', $formatted);
    }

    public function test_it_makes_ftp_clickable(): void
    {
        $formatted = Str::clickable('Test ftp://127.0.01 Test');

        $this->assertStringContainsString('<a href="ftp://127.0.01" rel="nofollow" target=\'_blank\'>ftp://127.0.01</a>', $formatted);
    }

    public function test_it_makes_email_clickable(): void
    {
        $formatted = Str::clickable('Test email@exampe.com Test');

        $this->assertStringContainsString('<a href="mailto:email@exampe.com">email@exampe.com</a>', $formatted);
    }
}
