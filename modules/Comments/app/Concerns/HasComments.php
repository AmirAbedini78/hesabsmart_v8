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

namespace Modules\Comments\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Comments\Models\Comment;
use Modules\Core\Models\Model;
use Modules\Users\Mention\PendingMention;

/** @mixin \Modules\Core\Models\Model */
trait HasComments
{
    /**
     * Boot the HasComments trait
     */
    protected static function bootHasComments(): void
    {
        static::deleting(function (Model $model) {
            if ($model->isReallyDeleting()) {
                $model->loadMissing('comments');

                $model->comments->each(function (Comment $comment) {
                    $comment->delete();
                });
            }
        });
    }

    /**
     * Get all of the model comments.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at');
    }

    /**
     * Add new comment for the commentable.
     */
    public function addComment(array $attributes): Comment
    {
        $mention = new PendingMention($attributes['body']);
        $attributes['body'] = $mention->getUpdatedText();

        $comment = $this->comments()->create($attributes);

        $comment->notifyMentionedUsers(
            $mention,
            $attributes['via_resource'] ?? null,
            $attributes['via_resource_id'] ?? null
        );

        return $comment->loadMissing('creator');
    }
}
