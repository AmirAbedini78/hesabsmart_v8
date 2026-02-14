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

use Closure;
use Illuminate\Contracts\Database\Query\Expression;
use JsonSerializable;
use Modules\Core\Common\Placeholders\GenericPlaceholder;
use Modules\Core\Contracts\Fields\Dateable;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Http\Resources\CustomFieldResource;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\Model;
use Modules\Core\Rules\UniqueResourceRule;
use Modules\Core\Support\HasHelpText;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

abstract class Field extends FieldElement implements JsonSerializable
{
    use DisplaysOnIndex,
        HasHelpText,
        HasValidationRules,
        ResolvesValue;

    /**
     * Default value.
     *
     * @var mixed
     */
    public $value;

    /**
     * Field attribute.
     *
     * @var string
     */
    public $attribute;

    /**
     * Custom field request attribute.
     *
     * @var string|null
     */
    public $requestAttribute;

    /**
     * Field label.
     *
     * @var string
     */
    public $label;

    /**
     * Indicates how the help text is displayed, as icon or text.
     */
    public string $helpTextDisplay = 'icon';

    /**
     * Whether the field is collapsed. E.q. view all fields.
     */
    public bool $collapsed = false;

    /**
     * Whether the field is primary.
     */
    public bool $primary = false;

    /**
     * Indicates whether the field is custom field.
     */
    public ?CustomField $customField = null;

    /**
     * Emit change event when field value changed.
     */
    public ?string $emitChangeEvent = null;

    /**
     * Is field read only.
     *
     * @var bool|callable
     */
    public $readonly = false;

    /**
     * Is the field hidden.
     */
    public bool $displayNone = false;

    /**
     * Indicates whether the column value should be always included in the JSON Resource.
     */
    public bool $alwaysInJsonResource = false;

    /**
     * Prepare for validation callback.
     *
     * @var callable|null
     */
    public $validationCallback;

    /**
     * Indicates whether the field is excluded from Zapier response.
     */
    public bool $excludeFromZapierResponse = false;

    /**
     * Field order.
     */
    public ?int $order;

    /**
     * Field column class. (full|half)
     */
    public string $width = 'full';

    /**
     * Field toggle indicator.
     */
    public bool $toggleable = false;

    /**
     * Custom callback used to determine if the field is required.
     *
     * @var \Closure|bool
     */
    public $isRequiredCallback;

    /**
     * Indicates whether field label is hidden on forms.
     */
    public bool $hideLabel = false;

    /**
     * Indicates whether field will be excluded from placeholders.
     */
    public bool $excludeFromPlaceholders = false;

    /**
     * Indicates whether a unique field can be unmarked as unique.
     */
    public bool $canUnmarkUnique = false;

    /**
     * Indicates that the field is available only for authRequired user.
     */
    public bool $authRequired = false;

    /**
     * The inline edit popover width (medium|large).
     */
    public string $inlineEditPanelWidth = 'medium';

    /**
     * Custom check if inline edit is disabled.
     *
     * @var bool|callable
     */
    public $disableInlineEdit = false;

    /**
     * Indicates whether the field is excluded from the special "bulk edit" action.
     */
    public bool $excludeFromBulkEdit = false;

    /**
     * Custom fill callback.
     *
     * @var null|callable
     */
    public $fillCallback = null;

    /**
     * The search column for the field.
     */
    public null|string|array|Expression $searchColumn = null;

    /**
     * Field component.
     */
    protected static $component = null;

    /**
     * Additional relationships to eager load when quering the resource.
     */
    public array $with = [];

    /**
     * Indicates if the field is excluded from index query.
     */
    public bool $excludeFromIndexQuery = false;

    /**
     * Indicates if the field is searchable.
     */
    protected bool $searchable = true;

    protected static array $formComponents = [];

    protected static array $detailComponents = [];

    protected static array $indexComponents = [];

    protected Field|array $inlineEditWith = [];

    protected static ?ResourceRequest $request = null;

    /**
     * Initialize new Field instance.
     *
     * @param  string  $attribute  field attribute
     * @param  string|null  $label  field label
     */
    public function __construct($attribute, $label = null)
    {
        $this->attribute = $attribute;

        $this->label = $label;
    }

    /**
     * Set field attribute.
     *
     * @param  string  $attribute
     */
    public function attribute($attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set field label.
     *
     * @param  string  $label
     */
    public function label($label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the field order.
     */
    public function order(?int $order): static
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set the field width for the form view.
     */
    public function width(string $width): static
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Mark the field as toggleable.
     */
    public function toggleable(bool $value = true): static
    {
        $this->toggleable = $value;

        return $this;
    }

    /**
     * Set default value on creation forms.
     *
     * @param  mixed  $value
     */
    public function withDefaultValue($value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Disable the field inline edit.
     */
    public function disableInlineEdit(bool|callable $value = true): static
    {
        $this->disableInlineEdit = $value;

        return $this;
    }

    /**
     * Check whether inline edit is disabled for the given model.
     */
    public function isInlineEditDisabled(Model $model): bool
    {
        if ($this->disableInlineEdit === true) {
            return true;
        }

        return is_callable($this->disableInlineEdit) && call_user_func_array($this->disableInlineEdit, [$model]);
    }

    /**
     * Get the field default value.
     */
    public function defaultValue(ResourceRequest $request): mixed
    {
        return with($this->value, function ($value) use ($request) {
            if ($value instanceof Closure) {
                return $value($request);
            }

            return $value;
        });
    }

    /**
     * Set collapsible field.
     */
    public function collapsed(bool $bool = true): static
    {
        $this->collapsed = $bool;

        return $this;
    }

    /**
     * Set the field display of the help text.
     */
    public function helpDisplay(string $display): static
    {
        $this->helpTextDisplay = $display;

        return $this;
    }

    /**
     * Add read only statement.
     */
    public function readonly(bool|callable $value): static
    {
        $this->readonly = $value;

        return $this;
    }

    /**
     * Determine whether the field is read only.
     */
    public function isReadonly(): bool
    {
        $callback = $this->readonly;

        return $callback === true || (is_callable($callback) && call_user_func($callback));
    }

    /**
     * Hides the field from the document.
     */
    public function displayNone(bool $value = true): static
    {
        $this->displayNone = $value;

        return $this;
    }

    /**
     * Indicates whether the field label should be displayed.
     */
    public function hideLabel(bool $value = true): static
    {
        $this->hideLabel = $value;

        return $this;
    }

    /**
     * Get the component name for the field.
     */
    public function component(): ?string
    {
        return static::$component;
    }

    /**
     * Get the field form component.
     */
    public function formComponent(): ?string
    {
        if (isset(static::$formComponents[static::class])) {
            return static::$formComponents[static::class];
        }

        if (! static::$component) {
            return null;
        }

        return 'form-'.static::$component;
    }

    /**
     * Get the field detail component.
     */
    public function detailComponent(): ?string
    {
        if (isset(static::$detailComponents[static::class])) {
            return static::$detailComponents[static::class];
        }

        if (! static::$component) {
            return null;
        }

        return 'detail-'.static::$component;
    }

    /**
     * Get the field index component.
     */
    public function indexComponent(): ?string
    {
        if (isset(static::$indexComponents[static::class])) {
            return static::$indexComponents[static::class];
        }

        if (! static::$component) {
            return null;
        }

        return 'index-'.static::$component;
    }

    /**
     * Get the fields to be used when editing inline.
     *
     * By default, it's the current instance.
     */
    public function inlineEditField(): null|array|Field
    {
        if (! empty($this->inlineEditWith)) {
            return $this->inlineEditWith;
        }

        return null;
    }

    /**
     * Add custom fields to perform inline edit.
     */
    public function inlineEditWith(Field|array $field)
    {
        $this->inlineEditWith = $field;

        return $this;
    }

    /**
     * Change the underlying field form component.
     */
    public static function useFormComponent(string $component): void
    {
        static::$formComponents[static::class] = $component;
    }

    /**
     * Change the underlying field detail component.
     */
    public static function useDetailComponent(string $component): void
    {
        static::$detailComponents[static::class] = $component;
    }

    /**
     * Change the underlying field index component.
     */
    public static function useIndexComponent(string $component): void
    {
        static::$indexComponents[static::class] = $component;
    }

    /**
     * Set the field as primary.
     */
    public function primary(bool $bool = true): static
    {
        $this->primary = $bool;

        return $this;
    }

    /**
     * Check whether the field is primary.
     */
    public function isPrimary(): bool
    {
        return $this->primary === true;
    }

    /**
     * Set the callback used to determine if the field is required.
     *
     * Useful when you have complex required validation requirements like filled, sometimes etc..,
     * you can manually mark the field as required by passing a boolean when defining the field.
     *
     * This method will only add a "required" indicator to the UI field.
     * You must still define the related requirement rules() that should apply during validation.
     *
     * @param  \Closure|bool  $callback
     */
    public function required($callback = true): static
    {
        $this->isRequiredCallback = $callback;

        return $this;
    }

    /**
     * Check whether the field is required based on the rules.
     */
    public function isRequired(ResourceRequest $request): bool
    {
        $callback = $this->isRequiredCallback;

        if ($callback === true || (is_callable($callback) && call_user_func($callback, $request))) {
            return true;
        }

        if (! empty($this->attribute)) {
            if ($request->isCreateRequest()) {
                $rules = $this->getCreationRules()[$this->requestAttribute()] ?? [];
            } elseif ($request->isUpdateRequest()) {
                $rules = $this->getUpdateRules()[$this->requestAttribute()] ?? [];
            } elseif ($request->isImportRequest()) {
                $rules = $this->getImportRules()[$this->requestAttribute()] ?? [];
            } else {
                $rules = $this->getRules()[$this->requestAttribute()] ?? [];
            }

            return in_array('required', $rules);
        }

        return false;
    }

    /**
     * Check whether the field is unique.
     */
    public function isUnique(): bool
    {
        foreach ($this->getRules() as $rules) {
            if (collect($rules)->whereInstanceOf(UniqueResourceRule::class)->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark the field as unique.
     */
    public function unique($model, $skipOnImport = true): static
    {
        $this->rules(
            UniqueResourceRule::make(
                $model, 'resourceId', $this->attribute
            )->skipOnImport($skipOnImport)
        );

        return $this;
    }

    /**
     * Mark the field as not unique.
     */
    public function notUnique(): static
    {
        foreach ($this->getRules() as $rules) {
            foreach ($rules as $ruleKey => $rule) {
                if ($rule instanceof UniqueResourceRule) {
                    unset($this->rules[$ruleKey]);
                }
            }
        }

        return $this;
    }

    /**
     * Mark that the a unique field can be marked as not unique via settings.
     */
    public function canUnmarkUnique(): static
    {
        $this->canUnmarkUnique = true;

        return $this;
    }

    /**
     * Exclude the field from the placeholders list.
     */
    public function excludeFromPlaceholders(): static
    {
        $this->excludeFromPlaceholders = true;

        return $this;
    }

    /**
     * Get the mailable template placeholder.
     *
     * @param  \Modules\Core\Models\Model|null  $model
     * @return \Modules\Core\Common\Placeholders\GenericPlaceholder|string
     */
    public function mailableTemplatePlaceholder($model)
    {
        return GenericPlaceholder::make($this->attribute)
            ->description($this->label)
            ->value(function () use ($model) {
                return $this->resolveForDisplay($model);
            });
    }

    /**
     * Provide a callable to prepare the field for validation.
     */
    public function prepareForValidation(callable $callable): static
    {
        $this->validationCallback = $callable;

        return $this;
    }

    /**
     * Indicates that the field value should be included in the JSON resource
     * when the user is not authorized to view the model/record.
     */
    public function showValueWhenUnauthorizedToView(): static
    {
        $this->alwaysInJsonResource = true;

        return $this;
    }

    /**
     * Indicates whether to emit change event when value is changed.
     */
    public function emitChangeEvent(?string $eventName = null): static
    {
        $this->emitChangeEvent = $eventName ?? 'field-'.$this->attribute.'-value-changed';

        return $this;
    }

    /**
     * Set whether to exclude the field from Zapier response.
     */
    public function excludeFromZapierResponse(): static
    {
        $this->excludeFromZapierResponse = true;

        return $this;
    }

    /**
     * Set the field custom field model.
     */
    public function setCustomField(?CustomField $field): static
    {
        $this->customField = $field;

        return $this;
    }

    /**
     * Check whether the current field is a custom field.
     */
    public function isCustomField(): bool
    {
        return ! is_null($this->customField);
    }

    /**
     * Get the field request attribute.
     *
     * @return string
     */
    public function requestAttribute()
    {
        return $this->requestAttribute ?? $this->attribute;
    }

    /**
     * Hydrate the model value.
     */
    public function fill(Model $model, string $attribute, ResourceRequest $request, string $requestAttribute): ?callable
    {
        $value = $this->attributeFromRequest($request, $requestAttribute);

        if (is_callable($this->fillCallback)) {
            return call_user_func_array($this->fillCallback, [
                $model,
                $attribute,
                $request,
                $value,
                $requestAttribute,
            ]);
        }

        $model->{$attribute} = $value;

        return null;
    }

    /**
     * Add custom fill callback for the field.
     */
    public function fillUsing(callable $callback): static
    {
        $this->fillCallback = $callback;

        return $this;
    }

    /**
     * Get the field value for the given request.
     */
    public function attributeFromRequest(ResourceRequest $request, string $requestAttribute): mixed
    {
        return $request->exists($requestAttribute) ? $request[$requestAttribute] : null;
    }

    /**
     * Check whether the field has options.
     */
    public function isOptionable(): bool
    {
        return $this->isMultiOptionable() || $this instanceof Optionable;
    }

    /**
     * Check whether the field is multi optionable.
     */
    public function isMultiOptionable(): bool
    {
        return $this instanceof MultiSelect || $this instanceof Checkbox;
    }

    /**
     * Mark the the field should be available only when there is an authenticated user.
     */
    public function authRequired(): static
    {
        $this->authRequired = true;

        return $this;
    }

    public function useSearchColumn(string|array|Expression $column)
    {
        $this->searchColumn = $column;

        return $this;
    }

    /**
     * Get the field search column.
     */
    public function searchColumn(): string|array|null
    {
        if ($this->searchable === false) {
            return null;
        }

        if ($this->searchColumn instanceof Expression) {
            return [$this->attribute => $this->searchColumn];
        }

        return $this->searchColumn ?? $this->attribute;
    }

    /**
     * Set if the field is searchable or not.
     */
    public function searchable(bool $value)
    {
        $this->searchable = $value;

        return $this;
    }

    /**
     * Prepare the field when it's intended to be used on the bulk edit action.
     */
    public function prepareForBulkEdit(): void
    {
        $this->rules = collect([...$this->rules, ...$this->updateRules])->unique()->all();
    }

    /**
     * Get the import data type.
     */
    public function importValueDataType(): string
    {
        return DataType::TYPE_STRING;
    }

    /**
     * Set the request for the field.
     */
    public static function setRequest(?ResourceRequest $request): void
    {
        static::$request = $request;
    }

    /**
     * Resolve the current request instance.
     *
     * @return ResourceRequest
     */
    protected function resolveRequest(): ResourceRequest
    {
        if (isset(static::$request)) {
            return static::$request;
        }

        return app(ResourceRequest::class);
    }

    /**
     * Serialize for front end.
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'component' => $this->component(),
            'formComponent' => $this->formComponent(),
            'detailComponent' => $this->detailComponent(),
            'indexComponent' => $this->indexComponent(),
            'inlineEditWith' => $this->inlineEditField(),
            'attribute' => $this->attribute,
            'label' => $this->label,
            'helpText' => $this->helpText,
            'helpTextDisplay' => $this->helpTextDisplay,
            'readonly' => $this->isReadonly(),
            'collapsed' => $this->collapsed,
            'primary' => $this->isPrimary(),
            'showOnIndex' => $this->showOnIndex,
            'showOnCreation' => $this->showOnCreation,
            'showOnUpdate' => $this->showOnUpdate,
            'showOnDetail' => $this->showOnDetail,
            'applicableForIndex' => $this->isApplicableForIndex(),
            'applicableForCreation' => $this->isApplicableForCreation(),
            'applicableForUpdate' => $this->isApplicableForUpdate(),
            'toggleable' => $this->toggleable,
            'displayNone' => $this->displayNone,
            'emitChangeEvent' => $this->emitChangeEvent,
            'width' => $this->width,
            'value' => $this->defaultValue($this->resolveRequest()),
            'isRequired' => $this->isRequired($this->resolveRequest()),
            'isUnique' => $this->isUnique(),
            'canUnmarkUnique' => $this->canUnmarkUnique,
            'inlineEditPanelWidth' => $this->inlineEditPanelWidth,
            'hideLabel' => $this->hideLabel,
            'customField' => $this->isCustomField() ? new CustomFieldResource($this->customField) : null,
            'dateable' => $this instanceof Dateable,
        ], $this->meta());
    }
}
