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

namespace Modules\Comments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Common\Media\HasMedia;
use Modules\Core\Concerns\HasCreator;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Models\Model;
use Modules\Users\Mention\PendingMention;

class Comment extends Model
{
    use HasCreator,
        HasFactory,
        HasMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'body',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_by' => 'int',
    ];

    /**
     * Get the parent commentable model
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the attributes that may contain pending media
     */
    public function textAttributesWithMedia(): string
    {
        return 'body';
    }

    /**
     * Notify the mentioned users for the given mention.
     *
     * @param  string|null  $viaResource
     * @param  int|null  $viaResourceId
     * @return void
     */
    public function notifyMentionedUsers(PendingMention $mention, $viaResource = null, $viaResourceId = null): static
    {
        $isViaResource = $viaResource && $viaResourceId;

        /** @var \Modules\Core\Contracts\Resources\Resourceable&\Modules\Core\Models\Model */
        $intermediate = $isViaResource ?
            Innoclapps::resourceByName($viaResource)->newModel()->find($viaResourceId) :
            $this->commentable;

        $mention->setUrl($intermediate::resource()->viewRouteFor($intermediate))->withUrlQueryParameter([
            ...[
                'comment_id' => $this->getKey(),
            ],
            ...array_filter([
                'section' => $isViaResource ? $this->commentable->resource()->name() : null,
                'resourceId' => $isViaResource ? $this->commentable->getKey() : null,
            ]),
        ])->notify();

        return $this;
    }
}
