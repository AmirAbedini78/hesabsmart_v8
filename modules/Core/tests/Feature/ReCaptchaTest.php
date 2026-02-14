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

use Modules\Core\Support\ReCaptcha;
use Tests\TestCase;

class ReCaptchaTest extends TestCase
{
    public function test_recaptcha_has_site_key(): void
    {
        $recaptcha = new ReCaptcha(app('request'));

        $recaptcha->setSiteKey('site-key');

        $this->assertEquals('site-key', $recaptcha->getSiteKey());
    }

    public function test_recaptcha_has_secret_key(): void
    {
        $recaptcha = new ReCaptcha(app('request'));

        $recaptcha->setSecretKey('secret-key');

        $this->assertEquals('secret-key', $recaptcha->getSecretKey());
    }

    public function test_recaptcha_has_skipped_ip_addresses(): void
    {
        $recaptcha = new ReCaptcha(app('request'));

        $recaptcha->setSkippedIps(['127.0.0.1', '127.0.0.0']);
        $this->assertEquals(['127.0.0.1', '127.0.0.0'], $recaptcha->getSkippedIps());

        // String with coma separation
        $recaptcha->setSkippedIps('127.0.0.1,127.0.0.0');
        $this->assertEquals(['127.0.0.1', '127.0.0.0'], $recaptcha->getSkippedIps());
    }

    public function test_recaptcha_can_determine_whether_to_skip_ip_address(): void
    {
        $request = app('request');
        $recaptcha = new ReCaptcha($request);

        $recaptcha->setSkippedIps(['127.0.0.1', '127.0.0.0', '12.2.2.2']);

        $this->assertTrue($recaptcha->shouldSkip('127.0.0.1'));
        $this->assertTrue($recaptcha->shouldSkip('127.0.0.0'));
        $this->assertFalse($recaptcha->shouldSkip('127.0.0.2'));

        $request->server->add(['REMOTE_ADDR' => '10.1.0.1']);
        $this->assertFalse($recaptcha->shouldSkip());

        $request->server->add(['REMOTE_ADDR' => '12.2.2.2']);
        $this->assertTrue($recaptcha->shouldSkip());
    }

    public function test_recaptcha_is_configured_when_has_secret_and_site_key(): void
    {
        $recaptcha = new ReCaptcha(app('request'));

        $recaptcha->setSiteKey('site-key');
        $recaptcha->setSecretKey('secret-key');

        $this->assertTrue($recaptcha->configured());
    }

    public function test_recaptcha_is_not_configured_when_doesnt_have_secret_and_site_key(): void
    {
        $recaptcha = new ReCaptcha(app('request'));

        $this->assertFalse($recaptcha->configured());

        $recaptcha->setSiteKey('site-key');

        $this->assertFalse($recaptcha->configured());
    }

    public function test_recaptcha_is_not_shown_when_doesnt_have_secret_and_site_key(): void
    {
        $request = app('request');
        $recaptcha = new ReCaptcha($request);

        $this->assertFalse($recaptcha->shouldShow());
    }

    public function test_can_determine_whether_recaptcha_should_be_shown(): void
    {
        $request = app('request');
        $recaptcha = new ReCaptcha($request);

        $recaptcha->setSiteKey('site-key');
        $recaptcha->setSecretKey('secret-key');

        $this->assertTrue($recaptcha->shouldShow());

        $recaptcha->setSkippedIps(['12.2.2.2']);

        $this->assertFalse($recaptcha->shouldShow('12.2.2.2'));
    }
}
