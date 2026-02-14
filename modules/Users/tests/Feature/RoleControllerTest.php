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

namespace Modules\Users\Tests\Feature;

use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    public function test_unauthenticated_cannot_access_roles_endpoints(): void
    {
        $role = $this->createRole('test');

        $this->getJson('/api/roles')->assertUnauthorized();
        $this->getJson('/api/roles/'.$role->id)->assertUnauthorized();
        $this->postJson('/api/roles')->assertUnauthorized();
        $this->putJson('/api/roles/'.$role->id)->assertUnauthorized();
        $this->deleteJson('/api/roles/'.$role->id)->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_roles_endpoints(): void
    {
        $this->asRegularUser()->signIn();
        $role = $this->createRole('test');

        $this->getJson('/api/roles')->assertForbidden();
        $this->getJson('/api/roles/'.$role->id)->assertForbidden();
        $this->postJson('/api/roles')->assertForbidden();
        $this->putJson('/api/roles/'.$role->id)->assertForbidden();
        $this->deleteJson('/api/roles/'.$role->id)->assertForbidden();
    }

    public function test_user_can_retrieve_all_roles(): void
    {
        $this->signIn();

        $this->createRole('admin');
        $this->createRole('writer');
        $this->createRole('manager');

        $this->getJson('/api/roles')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_user_can_create_role(): void
    {
        $this->signIn();

        $this->postJson('/api/roles', ['name' => 'Role'])
            ->assertStatus(201)
            ->assertJson(['name' => 'Role']);
    }

    public function test_user_can_update_role(): void
    {
        $this->signIn();

        $role = $this->createRole();

        $this->putJson(
            '/api/roles/'.$role->id,
            ['name' => 'Changed']
        )
            ->assertOk()
            ->assertJson(['name' => 'Changed']);
    }

    public function test_user_can_retrieve_role(): void
    {
        $this->signIn();

        $role = $this->createRole();

        $this->getJson('/api/roles/'.$role->id)->assertJson([
            'id' => $role->id,
            'name' => $role->name,
        ]);
    }

    public function test_user_can_delete_role(): void
    {
        $this->signIn();

        $role = $this->createRole();

        $this->deleteJson('/api/roles/'.$role->id)->assertNoContent();
        $this->assertModelMissing($role);
    }

    public function test_role_can_have_permissions(): void
    {
        $this->signIn();

        $permission = $this->createPermission();

        $this->postJson('/api/roles', [
            'name' => 'Role',
            'permissions' => [
                $permission->name,
            ],
        ])
            ->assertStatus(201)
            ->assertJsonCount(1, 'permissions')
            ->assertJsonPath('permissions.0.name', $permission->name);
    }

    public function test_role_permissions_can_be_updated(): void
    {
        $this->signIn();

        $role = $this->createRole();

        $permissions = [
            $this->createPermission('dummy-permission-1'),
            $this->createPermission('dummy-permission-2'),
            $this->createPermission('dummy-permission-3'),
        ];

        $role->givePermissionTo([
            $permissions[0]->name,
            $permissions[1]->name,
        ]);

        $this->putJson('/api/roles/'.$role->id, [
            'name' => $role->name,
            'permissions' => [$newPermissionName = $permissions[2]->name],
        ])
            ->assertOk()
            ->assertJsonCount(1, 'permissions')
            ->assertJsonPath('permissions.0.name', $newPermissionName);
    }

    public function test_role_requires_name(): void
    {
        $this->signIn();

        $role = $this->createRole('test');

        $this->postJson('/api/roles', ['name' => ''])
            ->assertJsonValidationErrors(['name']);

        $this->putJson('/api/roles/'.$role->id)->assertJsonValidationErrors(['name']);
    }

    public function test_role_name_must_be_unique(): void
    {
        $this->signIn();

        $roles = [
            $this->createRole('role-name-1'),
            $this->createRole('role-name-2'),
        ];

        $name = $roles[0]->name;

        $this->postJson('/api/roles', ['name' => $name])
            ->assertJsonValidationErrors(['name']);

        $id = $roles[1]->id;

        $this->putJson('/api/roles/'.$id, ['name' => $name])->assertJsonValidationErrors(['name']);
    }
}
