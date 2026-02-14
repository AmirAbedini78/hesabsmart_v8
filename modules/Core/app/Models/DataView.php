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
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Modules\Core\Database\Factories\DataViewFactory;
use Modules\Users\Models\User;

class DataView extends CacheModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'identifier', 'rules', 'is_shared', 'user_id', 'config',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_shared' => 'boolean',
        'is_single' => 'boolean',
        'user_id' => 'int',
        'rules' => 'array',
        'config' => AsArrayObject::class,
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::creating(function (DataView $model) {
            if (is_null($model->config)) {
                $model->config = [];
            }

            // We will check if the passed value is array and there are
            // children defined in the array, if not, we will assume the the
            // children is passed as one big array
            $groups = $model->rules;

            if (! is_array($groups)) {
                $groups = [];
            }

            // A group with only children or direct children provided?
            if (isset($groups[0]['type']) || isset($groups['type'])) {
                $groups = ['condition' => 'all', 'children' => $groups];
            }

            // Wrap in a group if not already group.
            if (isset($groups['condition'])) {
                $groups = [$groups];
            }

            $model->rules = $groups;
        });
    }

    /**
     * Get the view owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    /**
     * Get the view user config relation.
     */
    public function userConfig(): HasMany
    {
        return $this->hasMany(DataViewUserConfig::class);
    }

    /**
     * Check whether the view is system default.
     */
    public function isSystemDefault(): bool
    {
        return ! is_null($this->flag);
    }

    /**
     * Check whether the view is shared from another user for the current request.
     */
    public function isSharedFromAnotherUser(Request|User $request): bool
    {
        if ($this->isSystemDefault() || ! $this->is_shared) {
            return false;
        }

        $user = $request instanceof User ? $request : $request->user();

        return $this->user_id !== $user->getKey();
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

            $customKey = 'custom.view.'.$attributes['id'];

            if (Lang::has($customKey)) {
                return __($customKey);
            } elseif (Lang::has($value)) {
                return __($value);
            }

            return $value;
        });
    }

    /**
     * Set the view order for the given user.
     */
    public function setOrder(User $user, int $value): static
    {
        $config = $this->userConfig()->where('user_id', $user->getKey())->first() ??
            new DataViewUserConfig(['user_id' => $user->getKey()]);

        $config->display_order = $value;

        $this->userConfig()->save($config);

        return $this;
    }

    /**
     * Get the order of the view for the given user.
     */
    public function getOrderFor(User $user): ?int
    {
        return $this->userConfig->where('user_id', $user->getKey())->first()?->display_order;
    }

    /**
     * Set the view open state for the given user.
     */
    public function markAsOpen(User $user, bool $value = true): static
    {
        $config = $this->userConfig()->where('user_id', $user->getKey())->first() ??
        new DataViewUserConfig(['user_id' => $user->getKey()]);

        $config->is_open = $value;

        $this->userConfig()->save($config);

        return $this;
    }

    /**
     * Check whether the view is open for the given user.
     */
    public function isOpenFor(User $user): ?bool
    {
        return $this->userConfig->where('user_id', $user->getKey())->first()?->is_open;
    }

    /**
     * Scope a query to only include shared views.
     */
    public function scopeShared(Builder $query): void
    {
        $query->where('is_shared', true);
    }

    /**
     * Find view by flag.
     */
    public static function findByFlag(string $flag): ?DataView
    {
        return static::where('flag', $flag)->first();
    }

    /**
     * Scope a query to only include views of the given identifier.
     */
    public function scopeOfIdentifier(Builder $query, string $identifier): void
    {
        $query->where('identifier', $identifier);
    }

    /**
     * Scope a query to retrieve views for the given user.
     */
    public function scopeForUser(Builder $query, User|int $user, string $identifier): void
    {
        $userId = is_int($user) ? $user : $user->getKey();

        $query
            ->with(['userConfig' => fn (HasMany $query) => $query->where('user_id', $userId)])
            ->where(fn (Builder $query) => $query->whereNotNull('flag')
                ->orWhere('data_views.user_id', $userId)
                ->orWhere('is_shared', true)
                ->orWhere(fn (Builder $query) => $query->where('is_single', true)->whereNull('user_id'))
                ->orWhere(fn (Builder $query) => $query->where('is_single', true)->where('user_id', $userId))
            )
            ->ofIdentifier($identifier);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DataViewFactory
    {
        return new DataViewFactory;
    }
}
