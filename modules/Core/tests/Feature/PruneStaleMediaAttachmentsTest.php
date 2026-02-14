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

use Illuminate\Support\Carbon;
use Modules\Core\Common\Media\PruneStaleMediaAttachments;
use Modules\Core\Models\Media;
use Tests\TestCase;

class PruneStaleMediaAttachmentsTest extends TestCase
{
    public function test_it_prunes_stale_media_attachments(): void
    {
        Carbon::setTestNow(now()->subDay(1)->startOfDay());
        $media = Media::factory()->create();

        $pendingMedia = $media->markAsPending('draft-id');

        Carbon::setTestNow(null);

        (new PruneStaleMediaAttachments)();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertDatabaseMissing('pending_media_attachments', ['id' => $pendingMedia->id]);
    }
}
