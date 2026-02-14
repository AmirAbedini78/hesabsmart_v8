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

namespace Modules\WebForms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Concerns\HasCreator;
use Modules\Core\Concerns\HasInitialAttributes;
use Modules\Core\Concerns\HasUuid;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\ChecksForDuplicates;
use Modules\Core\Fields\Field;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Models\CacheModel;
use Modules\Deals\Models\Pipeline;
use Modules\Deals\Models\Stage;
use Modules\WebForms\Database\Factories\WebFormFactory;
use Modules\WebForms\Enums\WebFormSection;

class WebForm extends CacheModel
{
    use HasCreator,
        HasFactory,
        HasInitialAttributes,
        HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'status',
        'sections',
        'notifications',
        'styles',
        'submit_data',
        'title_prefix',
        'locale',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sections' => 'array',
        'notifications' => 'array',
        'styles' => 'array',
        'submit_data' => 'array',
        'total_submissions' => 'int',
        'user_id' => 'int',
        'created_by' => 'int',
    ];

    /**
     * Get all the web form deals
     */
    public function deals(): HasMany
    {
        return $this->hasMany(\Modules\Deals\Models\Deal::class);
    }

    /**
     * Get the web form owner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    /**
     * Get the web form public URL
     */
    protected function publicUrl(): Attribute
    {
        return Attribute::get(
            fn () => route('webform.view', $this->uuid)
        );
    }

    /**
     * Get the form submit data attribute
     */
    protected function submitData(): Attribute
    {
        return Attribute::get(function ($value) {
            $value = json_decode($value ?? '[]', true);
            $pipelineId = $value['pipeline_id'] ?? null;

            if (! $pipelineId || is_null(Pipeline::find($pipelineId))) {
                $pipeline = Pipeline::findPrimary();
                $value['pipeline_id'] = $pipeline->getKey();
                $value['stage_id'] = $pipeline->stages->first()->getKey();
            } elseif (is_null(Stage::find($value['stage_id']))) {
                $pipeline = Pipeline::find($pipelineId);
                $value['stage_id'] = $pipeline->stages->first()->getKey();
            }

            return $value;
        });
    }

    /**
     * Find field by resource
     */
    public function fieldByResource(string $attribute, string $resourceName): ?Field
    {
        return $this->fields()->first(function ($field) use ($attribute, $resourceName) {
            return $field->meta()['resourceName'] === $resourceName && $field->attribute === $attribute;
        });
    }

    /**
     * Find web for section
     */
    public function sections(WebFormSection $name): array
    {
        $sections = [];

        foreach ($this->sections as $section) {
            if (strtolower($section['type']) === strtolower($name->value)) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * Get all of the form file sections
     */
    public function fileSections(): array
    {
        return $this->sections(WebFormSection::FILE);
    }

    /**
     * Get file sections for the given resource
     */
    public function getFileSectionsForResource(string $resourceName): Collection
    {
        return collect($this->fileSections())->where('resourceName', $resourceName);
    }

    /**
     * Get all of the form field sections
     */
    public function fieldSections(): array
    {
        return $this->sections(WebFormSection::FIELD);
    }

    /**
     * Get the form submit section
     */
    public function submitSection(): ?array
    {
        return $this->sections(WebFormSection::SUBMIT)[0] ?? null;
    }

    /**
     * Get the form introduction section
     */
    public function introductionSection(): ?array
    {
        return $this->sections(WebFormSection::INTRODUCTION)[0] ?? null;
    }

    /**
     * Get the web form fields
     */
    public function fields(): FieldsCollection
    {
        return once(function () {
            $fields = new FieldsCollection([]);

            foreach ($this->fieldSections() as $section) {
                // Use getFields instead of resolveFields as the forms are available to admin
                // in case admin decide to add some field that canSee return false
                // won't be available here or in the request when resolving the fields
                $field = Innoclapps::resourceByName(
                    $section['resourceName']
                )->getFields()->find($section['attribute']);

                // Field removed?
                if (! $field) {
                    continue;
                }

                if ($field->isUnique()) {
                    $field->notUnique();
                }

                $field->requestAttribute = $section['requestAttribute'];

                // For front-end
                $field->withMeta([
                    'attribute' => $section['requestAttribute'],
                    // Used in the FormSubmissionService to find the field by resource
                    'resourceName' => $section['resourceName'],
                ])
                    ->label($section['label'])
                    ->help('')
                    ->canSee(fn () => true);

                if (in_array(ChecksForDuplicates::class, class_uses_recursive($field))) {
                    $field->disableDuplicateChecks();
                }

                if ($section['isRequired']) {
                    $field->rules('required');

                    if ($field->isOptionable() && method_exists($field, 'withoutClearAction')) {
                        $field->withoutClearAction();
                    }
                }

                $fields->push($field);
            }

            return $fields;
        });
    }

    /**
     * Check whether the form is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the web form logo URL
     */
    public function logo(): ?string
    {
        $logo = $this->styles['logo'] ?? null;

        if ($logo) {
            return config('core.logo.'.$logo);
        }

        return null;
    }

    /**
     * Get the form status.
     */
    protected function status(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) $value === 0 ? 'inactive' : 'active',
            set: fn ($value) => $value === 'active' ? 1 : 0
        );
    }

    /**
     * Get the model initial attributes with dot notation
     */
    public static function getInitialAttributes(): array
    {
        return [
            'sections' => [],
            'notifications' => [],
            'submit_data.action' => 'message',
            'submit_data.success_title' => 'Form submitted.',
            'styles.logo' => null,
            'status' => 'active',
            // Perhaps UNIT tests?
            'locale' => Auth::user()?->preferredLocale(),
            'user_id' => Auth::check() ? Auth::id() : null,
        ];
    }

    /**
     * Find webform by the given uuid.
     */
    public static function findByUuid(string $uuid): WebForm
    {
        return static::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Eager load the relations that are required for the front end response.
     */
    public function scopeWithCommon(Builder $query): void
    {
        $query->with('user');
    }

    /**
     * Add the form section field
     */
    public function addFieldToFieldSections(): static
    {
        $sections = [];

        foreach ($this->sections as $key => $section) {
            switch ($section['type']) {
                case WebFormSection::FIELD->value:

                    $sections[$key] = array_merge($section, [
                        'field' => $this->fieldByResource(
                            $section['attribute'],
                            $section['resourceName']
                        ),
                    ]);

                    break;
                default:
                    $sections[$key] = $section;

                    break;
            }
        }

        $this->setAttribute('sections', $sections);

        return $this;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): WebFormFactory
    {
        return WebFormFactory::new();
    }
}
