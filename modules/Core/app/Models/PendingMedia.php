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

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingMedia extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pending_media_attachments';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'media_id' => 'int',
    ];

    /**
     * A pending media has attachment/media
     */
    public function attachment(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Media::class, 'media_id');
    }

    /**
     * Unmark the pending media as pending.
     *
     * @param  string  $destination  The destination path to move the media.
     */
    public function unmarkAsPending(string $destination, ?string $filename = null)
    {
        $this->attachment->move($destination, $filename);

        $this->delete();
    }

    /**
     * Scope a query to only include pending media by given draft id.
     */
    public function scopeOfDraftId(Builder $query, string $draftId): void
    {
        $query->where('draft_id', $draftId);
    }

    /**
     * Purge the pending media.
     */
    public function purge(): bool
    {
        $this->attachment->delete();

        return $this->delete();
    }
}
