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

namespace Modules\Notes\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Notes\Models\Note;
use Modules\Users\Models\User;

class NotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any notes.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the note.
     */
    public function view(User $user, Note $note): bool
    {
        return (int) $user->id === (int) $note->user_id;
    }

    /**
     * Determine if the given user can create notes.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the note.
     */
    public function update(User $user, Note $note): bool
    {
        return (int) $user->id === (int) $note->user_id;
    }

    /**
     * Determine whether the user can delete the note.
     */
    public function delete(User $user, Note $note): bool
    {
        return (int) $user->id === (int) $note->user_id;
    }

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        $request = app(ResourceRequest::class);

        if ($ability === 'view' && $request->viaResource()) {
            $related = $request
                ->findResource($request->get('via_resource'))
                ->newQuery()
                ->whereHas('notes', fn (Builder $query) => $query->where('id', $request->route()->parameter('resourceId')))
                ->find($request->get('via_resource_id'));

            if ($related) {
                return Gate::allows('view', $related);
            }
        }

        return null;
    }
}
