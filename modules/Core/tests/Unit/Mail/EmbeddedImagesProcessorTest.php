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

namespace Modules\Core\Tests\Unit\Mail;

use Modules\Core\Common\Mail\EmbeddedImagesProcessor;
use PHPUnit\Framework\TestCase;

class EmbeddedImagesProcessorTest extends TestCase
{
    public function test_it_does_process_embedded_images(): void
    {
        $content = '<p>Content<img src="data:image/jpeg;base64, LzlqLzRBQ... <!-- Base64 data -->"><br />Content</p>';
        $processed = (new EmbeddedImagesProcessor)($content, fn () => 'cid:test');

        $this->assertStringContainsString('src="cid:test"', $processed);
    }

    public function test_it_does_call_the_callback_when_processing_embedded_images(): void
    {
        $content = '<p>Content<img src="data:image/jpeg;base64, LzlqLzRBQ... <!-- Base64 data -->"><br />Content</p>';
        $mime = null;

        (new EmbeddedImagesProcessor)($content, function ($data, $name, $contentType) use (&$mime) {
            $mime = $contentType;
        });

        $this->assertSame('image/jpeg', $mime);
    }

    public function test_it_does_not_process_non_base64_images(): void
    {
        $content = '<p>Content<img src="https://concordcrm.com/image.jpg"><br />Content</p>';
        $processed = (new EmbeddedImagesProcessor)($content, fn () => null);

        $this->assertSame($content, $processed);
    }

    public function test_it_does_not_process_images_if_body_is_null(): void
    {
        $body = (new EmbeddedImagesProcessor)(null, fn () => null);

        $this->assertNull($body);
    }
}
