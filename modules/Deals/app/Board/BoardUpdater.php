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

namespace Modules\Deals\Board;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Deals\Events\DealMovedToStage;
use Modules\Deals\Models\Deal;
use Modules\Deals\Models\Stage;
use Modules\Users\Models\User;

class BoardUpdater
{
    /**
     * Caches the workable deals
     *
     * @see deals method
     */
    protected ?Collection $deals = null;

    /**
     * Stages cache
     */
    protected Collection $stages;

    /**
     * The payload data
     */
    protected Collection $data;

    /**
     * Initialize new BoardUpdater instance.
     */
    public function __construct(array $data, protected User $user)
    {
        $this->data = collect($data);
        $this->stages = Stage::get();
    }

    /**
     * Performs the update
     */
    public function perform(): void
    {
        $data = $this->filterAuthorizedDeals();
        $order = $data->mapWithKeys(fn (array $data) => [$data['id'] => $data['board_order']]);

        // We will map the appropriate data for the Batch
        // so we can perform the update without any injected fields
        $attributes = $data->map(function (array $attrs) {
            $model = $this->deals($attrs['id']);
            $stageId = (int) $attrs['stage_id'];

            $updatedAt = $model->stage_id === $stageId ? $model->updated_at : now();

            return [
                'id' => (int) $attrs['id'],
                'stage_id' => $stageId,
                'swatch_color' => $attrs['swatch_color'],
                'board_order' => $attrs['board_order'],
                'updated_at' => $updatedAt->format($model->getDateFormat()),
            ];
        })->all();

        $this->triggerMovedToStageEventIfNeeded($attributes);

        $this->update($attributes);

        $this->ensureNotVisibleDealsAreSortedAsLast($attributes[0]['stage_id'] ?? null, $order);
    }

    protected function ensureNotVisibleDealsAreSortedAsLast($stageId, $data): void
    {
        if (! $stageId) {
            return;
        }

        $max = $data->flatten()->max();
        $allIds = $data->keys()->all();

        Deal::whereNotIn('id', $allIds)
            ->where('stage_id', $stageId)
            ->update(['board_order' => DB::raw("$max+board_order")]);
    }

    /**
     * Update the deals from the payload
     *
     * If we change this method to not perform the update via batch,
     * check the DealObserver because in the updated event the the LogDealMovedToStageActivity
     * listener is triggered too
     */
    protected function update(array $data): void
    {
        Deal::$boardFiresEvents = true;
        $this->fireModelsUpdatingEvent($data);
        batch()->update(new Deal, $data);
        $this->fireModelsUpdatedEvent($data);
        Deal::$boardFiresEvents = false;
    }

    /**
     * Get the deals based on the id's provided in the payload|data
     */
    protected function deals($id = null): Deal|Collection
    {
        if (! $this->deals) {
            $this->deals = Deal::with(['pipeline', 'user'])->findMany(
                $this->data->pluck('id')->all()
            );
        }

        if ($id) {
            return $this->deals->find($id);
        }

        return $this->deals;
    }

    /**
     * Trigger the deal moved to stage event if needed
     */
    protected function triggerMovedToStageEventIfNeeded(array $deals): void
    {
        foreach ($deals as $data) {
            $deal = $this->deals($data['id']);

            if ($deal->stage_id !== $data['stage_id']) {
                $oldStage = $this->stages->find($deal->stage_id);

                // Update with the new stage data
                $deal->setRelation('stage', $this->stages->find($data['stage_id']));
                $deal->stage_id = $data['stage_id'];

                event(new DealMovedToStage($deal, $oldStage));
            }
        }
    }

    /**
     * Fire the models being updated "updating" event.
     */
    protected function fireModelsUpdatingEvent(array $data): void
    {
        foreach ($data as $attributes) {
            $deal = $this->deals($attributes['id']);

            $deal->forceFill($attributes);

            $deal->getEventDispatcher()->dispatch('eloquent.updating: '.$deal::class, $deal);
        }
    }

    /**
     * Fire the models being updated "updated" event.
     */
    protected function fireModelsUpdatedEvent(array $data): void
    {
        foreach ($data as $attributes) {
            $deal = $this->deals($attributes['id']);

            // Ensure "wasChanged()" checks works correctly.
            $deal->syncChanges();

            $deal->getEventDispatcher()->dispatch('eloquent.updated: '.$deal::class, $deal);
        }
    }

    /**
     * Remove any deals which the user is not authorized to update
     */
    protected function filterAuthorizedDeals(): Collection
    {
        return $this->data->reject(
            fn ($data) => $this->user->cant('update', $this->deals($data['id']))
        );
    }
}
