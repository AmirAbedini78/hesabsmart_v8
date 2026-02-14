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

namespace Modules\Users\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Concerns\HasUuid;
use Modules\Core\Models\Model;
use Modules\Users\Database\Factories\UserInvitationFactory;

class UserInvitation extends Model
{
    use HasFactory;
    use HasUuid;

    /**
     * The appended attributes.
     *
     * @var array
     */
    protected $appends = ['link'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['email', 'roles', 'teams', 'super_admin', 'access_api'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'super_admin' => 'boolean',
        'access_api' => 'boolean',
        'roles' => 'array',
        'teams' => 'array',
    ];

    /**
     * Get the invitation link.
     */
    protected function link(): Attribute
    {
        return Attribute::get(
            fn () => route('invitation.show', $this->token)
        );
    }

    /**
     * Get the model uuid column name.
     */
    public function uuidColumn(): string
    {
        return 'token';
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserInvitationFactory
    {
        return UserInvitationFactory::new();
    }
}
