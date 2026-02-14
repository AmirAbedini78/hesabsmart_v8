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

namespace Modules\Core\Fields;

use Modules\Core\Common\Placeholders\GenericPlaceholder;
use Modules\Core\Contracts\Fields\Deleteable;
use Modules\Core\Fields\Deleteable as DeleteableTrait;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Users\Mention\PendingMention;

class Editor extends Field implements Deleteable
{
    use DeleteableTrait;

    /**
     * Field component.
     */
    protected static $component = 'editor-field';

    /**
     * The inline edit popover width (medium|large).
     */
    public string $inlineEditPanelWidth = 'large';

    /**
     * Indicates whether the mentions feature is enabled for the editor.
     *
     * @var bool|callable
     */
    public $withMentions = false;

    /**
     * Initialize new Editor instance.
     */
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this
            ->deleteUsing(function (Model $model) {
                // as an example
            })
            ->fillUsing(function (Model $model, string $attribute, ResourceRequest $request, ?string $value) {
                $model->{$attribute} = $value;

                if ($this->mentionsEnabled()) {
                    $mention = new PendingMention($value ?: '');

                    if ($mention->hasMentions()) {
                        $value = $mention->getUpdatedText();
                    }

                    $model->{$attribute} = $value;

                    return function () use ($mention, $model, $request) {
                        /** @var \Modules\Core\Contracts\Resources\Resourceable&\Modules\Core\Models\Model */
                        $intermediate = $request->viaResource() ?
                            $request->findResource($request->via_resource)->newQuery()->find($request->via_resource_id) :
                            $model;

                        $mention->setUrl($intermediate::resource()->viewRouteFor($intermediate))->withUrlQueryParameter([
                            'section' => $request->viaResource() ? $model->resource()->name() : null,
                            'resourceId' => $request->viaResource() ? $model->getKey() : null,
                        ])->notify();
                    };
                }
            })
            ->resolveUsing(fn ($model, $attribute) => clean($model->{$attribute}));
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Modules\Core\Models\Model|null  $model
     * @return \Modules\Core\Common\Placeholders\GenericPlaceholder
     */
    public function mailableTemplatePlaceholder($model)
    {
        return GenericPlaceholder::make($this->attribute)
            ->description($this->label)
            ->withStartInterpolation('{{{')
            ->withEndInterpolation('}}}')
            ->value(fn () => $this->resolveForDisplay($model));
    }

    /**
     * Add mention support to the editor.
     */
    public function withMentions(bool|callable $value = true): static
    {
        $this->withMentions = $value;

        return $this;
    }

    /**
     * Check whether mentions are enabled for the field.
     */
    public function mentionsEnabled(): bool
    {
        $callback = $this->withMentions;

        return $callback === true || (is_callable($callback) && call_user_func($callback));
    }

    /**
     * Mark the editor as mininmal.
     */
    public function minimal(): static
    {
        $this->withMeta([
            'attributes' => [
                'minimal' => true,
            ],
        ]);

        return $this;
    }

    /**
     * Prepare the field when it's intended to be used on the bulk edit action.
     */
    public function prepareForBulkEdit(): void
    {
        $this->withMentions = false;

        parent::prepareForBulkEdit();
    }

    /**
     * Serialize for front end.
     */
    public function jsonSerialize(): array
    {
        $this->withMeta([
            'attributes' => [
                'with-mention' => $this->mentionsEnabled(),
            ],
        ]);

        return parent::jsonSerialize();
    }
}
