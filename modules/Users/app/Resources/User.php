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

namespace Modules\Users\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Fields\BelongsToMany;
use Modules\Core\Fields\Boolean;
use Modules\Core\Fields\CreatedAt;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Fields\ID;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\Timezone;
use Modules\Core\Fields\UpdatedAt;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Models\Role;
use Modules\Core\Resource\Resource;
use Modules\Core\Rules\StringRule;
use Modules\Core\Rules\UniqueResourceRule;
use Modules\Core\Rules\ValidLocaleRule;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Table\Column;
use Modules\Core\Table\Table;
use Modules\Users\Http\Resources\UserResource;
use Modules\Users\Services\UserService;

class User extends Resource implements Tableable, WithResourceRoutes
{
    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Users\Models\User';

    /**
     * Provide the resource table class instance.
     */
    public function table(Builder $query, ResourceRequest $request, string $identifier): Table
    {
        return Table::make($query, $request, $identifier)
            ->select(['avatar', 'super_admin'])
            ->appends(['avatar_url'])
            ->with(['teams', 'managedTeams'])
            ->withViews()
            ->singleView()
            ->orderBy(static::$orderBy, static::$orderByDir);
    }

    /**
     * Get the resource search columns.
     */
    public function searchableColumns(): array
    {
        return ['name' => 'like', 'email'];
    }

    /**
     * Get the fields for index.
     */
    public function fieldsForIndex(): FieldsCollection
    {
        $indexFields = [
            Text::make('name', __('users::user.name'))
                ->tapIndexColumn(fn (Column $column) => $column
                    ->width('300px')
                    ->route('/settings/users/{id}/edit')
                    ->primary()),
            ID::make(),
            Text::make('email', __('users::user.email'))->tapIndexColumn(
                fn (Column $column) => $column->link('mailto:{email}')
            ),
            BelongsToMany::make('roles', __('core::role.roles'))
                ->labelKey('name')
                ->displayAsBadges()
                ->hidden(),
            BelongsToMany::make('teams', __('users::team.teams'))
                ->labelKey('name')
                ->displayAsBadges()
                ->hidden(),
            Timezone::make('timezone', __('core::app.timezone'))->hidden(),
            Boolean::make('super_admin', __('users::user.super_admin')),
            Boolean::make('access_api', __('core::api.access'))->hidden(),
            CreatedAt::make()->hidden(),
            UpdatedAt::make()->hidden(),
        ];

        return $this->resolveFields()
            ->push(...$indexFields)
            ->disableInlineEdit()
            ->filterForIndex();
    }

    /**
     * Create resource record.
     */
    public function create(Model $model, ResourceRequest $request): Model
    {
        return (new UserService)->create($model, $request->all());
    }

    /**
     * Update resource record.
     */
    public function update(Model $model, ResourceRequest $request): Model
    {
        return (new UserService)->update($model, $request->all());
    }

    /**
     * Delete resource record.
     */
    public function delete(Model $model, ResourceRequest $request): bool
    {
        $transferDataTo = $request->integer('transfer_data_to') ?: null;

        return (new UserService)->delete($model, $transferDataTo);
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return UserResource::class;
    }

    /**
     * Get the resource rules available for create and update
     */
    public function rules(ResourceRequest $request): array
    {
        return [
            'name' => ['required', StringRule::make()],
            'password' => [
                $request->route('resourceId') ? 'nullable' : 'required', 'confirmed', 'min:6',
            ],
            'roles' => ['sometimes', 'array', Rule::in(Role::select('name')->get()->pluck('name')->all())],
            'email' => ['required', StringRule::make(), 'email', UniqueResourceRule::make(static::$model)],
            'locale' => ['nullable', new ValidLocaleRule],
            'timezone' => ['required', 'string', 'timezone:all'],
            'time_format' => ['required', 'string', Rule::in(config('core.time_formats'))],
            'date_format' => ['required', 'string', Rule::in(config('core.date_formats'))],
            'default_landing_page' => ['nullable', StringRule::make()],
        ];
    }

    /**
     * Provides the resource available actions
     */
    public function actions(ResourceRequest $request): array
    {
        return [
            (new \Modules\Users\Actions\UserDelete)->canSeeWhen('is-super-admin'),
        ];
    }

    /**
     * Register the settings menu items for the resource
     */
    public function settingsMenu(): array
    {
        return [
            SettingsMenuItem::make($this->name(), __('users::user.users'))
                ->path('/users')
                ->icon('Users')
                ->order(41),
        ];
    }
}
