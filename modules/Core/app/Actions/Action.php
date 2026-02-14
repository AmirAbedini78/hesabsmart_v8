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

namespace Modules\Core\Actions;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Support\Authorizeable;
use Modules\Core\Support\Makeable;

class Action implements JsonSerializable
{
    use Authorizeable,
        Makeable;

    /**
     * Indicates that the action will be shown on the index view.
     */
    public bool $showOnIndex = true;

    /**
     * Indicates that the action will be shown on the detail view.
     */
    public bool $showOnDetail = true;

    /**
     * Indicates that the action will be shown inline on table row.
     */
    public bool $showInline = false;

    /**
     * Indicates that the action does not have confirmation dialog.
     */
    public bool $withoutConfirmation = false;

    /**
     * The action modal size. (sm, md, lg, xl, xxl)
     */
    public string $size = 'sm';

    /**
     * The count of the models user selected for the action.
     */
    public int $total = 0;

    /**
     * The XHR response type that should be passed from the front-end.
     */
    public string $responseType = 'json';

    /**
     * The action humanized name.
     */
    public ?string $name = null;

    /**
     * Indicates if the action intended to be run on one resource only.
     */
    public bool $sole = false;

    /**
     * Action authorization callback.
     *
     * @var null|\Closure(ActionRequest, mixed, int):bool
     */
    public $canRunCallback = null;

    /**
     * Action execution callback.
     *
     * @var null|\Closure(Collection, ActionFields):mixed
     */
    public $executeCallback = null;

    public ?string $floatResource = null;

    /**
     * Indicates whether this action is destroyable.
     */
    protected bool $destroyable = false;

    /**
     * Initialize new Action instance.
     */
    public function __construct(protected ?string $key = null) {}

    /**
     * Handle method that all actions must implement.
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        return is_callable($this->executeCallback) ? call_user_func($this->executeCallback, $models, $fields) : [];
    }

    /**
     * Create new Closure action.
     */
    public static function using(string $key, string $name, Closure $callback)
    {
        $instance = new static($key);

        $instance->name = $name;

        return $instance->executeUsing($callback);
    }

    /**
     * Get the action fields.
     */
    public function fields(ResourceRequest $request): array
    {
        return [];
    }

    /**
     * Resolve action fields.
     */
    public function resolveFields(ResourceRequest $request): FieldsCollection
    {
        return (new FieldsCollection($this->fields($request)))->authorized();
    }

    /**
     * Determine if the action can be executed for the given request.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        return is_callable($this->canRunCallback) ?
            call_user_func($this->canRunCallback, $request, $model, $this->total) :
            true;
    }

    /**
     * Add custom authorization callback for the action.
     *
     * @param  \Closure(ActionRequest, mixed, int):bool  $callback
     */
    public function canRun(Closure $callback): static
    {
        $this->canRunCallback = $callback;

        return $this;
    }

    /**
     * Add custom execution callback for the action.
     *
     * @param  \Closure(Collection, ActionFields):mixed  $callback
     */
    public function executeUsing(Closure $callback): static
    {
        $this->executeCallback = $callback;

        return $this;
    }

    /**
     * Run action based on the request data.
     *
     * @return mixed
     */
    public function run(ActionRequest $request, Builder $query)
    {
        $ids = $request->input('ids', []);
        $fields = $request->resolveFields();

        $this->total = count($ids);

        /**
         * Ensure multiple models cannot be executed on sole actions.
         */
        if ($this->sole === true && $this->total > 1) {
            return static::error('Please run this action only on one resource.');
        }

        /**
         * Find all models and exclude any models that are not authorized to be handled in this action
         */
        $models = $this->filterForExecution(
            $this->findModelsForExecution($ids, $query),
            $request
        );

        /**
         * All models excluded? In this case, the user is probably not authorized to run the action
         */
        if ($models->count() === 0) {
            return static::error(__('users::user.not_authorized'));
        } elseif ($models->count() > (int) config('core.actions.disable_notifications_more_than')) {
            Innoclapps::muteAllCommunicationChannels();
        }

        $response = $this->handle($models, $fields);

        return is_null($response) ?
            static::success(__('core::actions.run_successfully')) :
            $response;
    }

    /**
     * Set that the action should be run on one resource only.
     */
    public function sole(): static
    {
        $this->sole = true;

        return $this;
    }

    /**
     * Set the action modal size.
     */
    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Toasted success alert.
     */
    public static function success(string $message): array
    {
        return ['success' => $message];
    }

    /**
     * Toasted info alert.
     */
    public static function info(string $message): array
    {
        return ['info' => $message];
    }

    /**
     * Toasted success alert.
     */
    public static function error(string $message): array
    {
        return ['error' => $message];
    }

    /**
     * Throw confetti on success.
     */
    public static function confetti(): array
    {
        return ['confetti' => true];
    }

    /**
     * Return an open new tab response from the action.
     */
    public static function openInNewTab(string $url): array
    {
        return ['openInNewTab' => $url];
    }

    /**
     * Return an route navigation response from the action.
     */
    public static function navigateTo(string $url): array
    {
        return ['navigateTo' => $url];
    }

    /**
     * Provide action humanized name.
     */
    public function name(): string
    {
        return $this->name ?: Str::title(Str::snake(get_called_class(), ' '));
    }

    /**
     * Get the URI key for the card.
     */
    public function uriKey(): string
    {
        return $this->key ?: Str::replaceLast('-action', '', Str::kebab(class_basename(get_called_class())));
    }

    /**
     * Get the action confirmation message.
     */
    public function confirmMessage(): string
    {
        return __('core::actions.confirmation_message');
    }

    /**
     * Get the action confirmation button text.
     */
    public function confirmButtonText(): string
    {
        return __('core::app.confirm');
    }

    /**
     * Get the action cancel button text.
     */
    public function cancelButtonText(): string
    {
        return __('core::app.cancel');
    }

    /**
     * Set the action to not have confirmation dialog.
     */
    public function withoutConfirmation(): static
    {
        $this->withoutConfirmation = true;

        return $this;
    }

    /**
     * Get the action modal confirmation component.
     */
    public function component(): string
    {
        return 'action-modal';
    }

    /**
     * Indicate that the action is only available on the table row.
     */
    public function onlyInline($value = true): static
    {
        $this->showInline = $value;
        $this->showOnIndex = ! $value;
        $this->showOnDetail = ! $value;

        return $this;
    }

    /**
     * Display the action inline on table row.
     */
    public function showInline(): static
    {
        $this->showInline = true;

        return $this;
    }

    /**
     * Set the action to be available only on index view.
     */
    public function onlyOnIndex(): static
    {
        $this->showOnIndex = true;
        $this->showOnDetail = false;
        $this->showInline = false;

        return $this;
    }

    /**
     * Set the action to be available only on detail view.
     */
    public function onlyOnDetail(): static
    {
        $this->showOnDetail = true;
        $this->showOnIndex = false;
        $this->showInline = false;

        return $this;
    }

    public function floatResourceInEditMode(): static
    {
        if (! $this->canRunCallback) {
            $this->canRun(function (ActionRequest $request, Model $model) {
                return $request->user()->can('edit', $model);
            });
        }

        $this->configureFloatingResource('edit');

        $this->name = __('core::app.edit');

        return $this;
    }

    public function floatResourceInDetailMode(): static
    {
        $this->configureFloatingResource('detail');
        $this->name = __('core::app.view_record');

        return $this;
    }

    protected function configureFloatingResource(string $mode): void
    {
        $this->withoutConfirmation();
        $this->onlyInline();
        $this->sole();

        $this->floatResource = $mode;
    }

    /**
     * Query the models for execution
     */
    protected function findModelsForExecution(array $ids, Builder $query): EloquentCollection
    {
        return $query->findMany($ids);
    }

    /**
     * Filter models for exeuction.
     */
    protected function filterForExecution(Collection $models, ActionRequest $request): Collection
    {
        return $models->filter(fn (Model $model) => $this->authorizedToRun($request, $model));
    }

    /**
     * jsonSerialize.
     */
    public function jsonSerialize(): array
    {
        $fields = $this->resolveFields(app(ResourceRequest::class));

        return [
            'name' => $this->name(),
            'confirmMessage' => count($fields) === 0 ? $this->confirmMessage() : null,
            'confirmButtonText' => $this->confirmButtonText(),
            'cancelButtonText' => $this->cancelButtonText(),
            'component' => $this->component(),
            'destroyable' => $this->destroyable,
            'withoutConfirmation' => $this->withoutConfirmation,
            'fields' => $fields,
            'showInline' => $this->showInline,
            'showOnIndex' => $this->showOnIndex,
            'showOnDetail' => $this->showOnDetail,
            'responseType' => $this->responseType,
            'size' => $this->size,
            'sole' => $this->sole,
            'floatResource' => $this->floatResource,
            'uriKey' => $this->uriKey(),
        ];
    }
}
