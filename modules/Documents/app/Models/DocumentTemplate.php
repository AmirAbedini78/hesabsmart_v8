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

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Modules\Core\Contracts\Resources\Resourceable as ResourceableContract;
use Modules\Core\Models\CacheModel;
use Modules\Core\Resource\Resourceable;
use Modules\Documents\Content\FontsExtractor;
use Modules\Documents\Database\Factories\DocumentTemplateFactory;
use Modules\Documents\Enums\DocumentViewType;

class DocumentTemplate extends CacheModel implements ResourceableContract
{
    use HasFactory,
        Resourceable;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_shared' => 'bool',
        'user_id' => 'int',
        'view_type' => DocumentViewType::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'content', 'view_type', 'is_shared',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::creating(function (DocumentTemplate $model) {
            $model->user_id = $model->user_id ?? Auth::id();
        });
    }

    /**
     * Get the template owner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    /**
     * Scope a query to only include shared templates.
     */
    public function scopeShared(Builder $query): void
    {
        $query->where('is_shared', true);
    }

    /**
     * Get all of the used Google fonts in the template content
     */
    public function usedGoogleFonts(): Collection
    {
        return (new FontsExtractor)->extractGoogleFonts($this->content ?: '');
    }

    /**
     * Clone the template.
     */
    public function clone(int $userId): DocumentTemplate
    {
        $newTemplate = $this->replicate();

        $newTemplate->name = clone_prefix($this->name);

        $newTemplate->user_id = $userId;
        $newTemplate->is_shared = false;

        $newTemplate->save();

        return $newTemplate;
    }

    /**
     * Name attribute accessor
     *
     * Supports translation from language file
     */
    protected function name(): Attribute
    {
        return Attribute::get(function (?string $value, array $attributes) {
            if (! array_key_exists('id', $attributes)) {
                return $value;
            }

            $customKey = 'custom.document_template.'.$attributes['id'];

            if (Lang::has($customKey)) {
                return __($customKey);
            } elseif (Lang::has($value)) {
                return __($value);
            }

            return $value;
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DocumentTemplateFactory
    {
        return DocumentTemplateFactory::new();
    }
}
