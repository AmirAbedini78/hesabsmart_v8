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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Core\Database\Factories\ModelVisibilityGroupFactory;

class ModelVisibilityGroup extends CacheModel
{
    use HasFactory;

    protected string $dependsTable = 'model_visibility_group_dependents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['type'];

    /**
     * Indicates if the model has timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get all of the teams that belongs to the visibility group.
     */
    public function teams(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Users\Models\Team::class, 'dependable', $this->dependsTable);
    }

    /**
     * Get all of the users that belongs to the visibility group.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Users\Models\User::class, 'dependable', $this->dependsTable);
    }

    /**
     * Get the parent model which uses visibility dependents.
     */
    public function visibilityable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ModelVisibilityGroupFactory
    {
        return ModelVisibilityGroupFactory::new();
    }
}
