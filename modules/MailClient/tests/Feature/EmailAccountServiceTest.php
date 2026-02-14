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

namespace Modules\MailClient\Tests\Feature;

use Illuminate\Support\Facades\Request;
use Modules\MailClient\Models\EmailAccount;
use Modules\MailClient\Models\EmailAccountFolder;
use Modules\MailClient\Services\EmailAccountService;
use Tests\TestCase;

class EmailAccountServiceTest extends TestCase
{
    public function test_account_is_marked_as_personal_when_user_id_is_provided(): void
    {
        $user = $this->signIn();
        $payload = EmailAccount::factory()->personal($user)->raw();

        $account = (new EmailAccountService)->create($payload);

        $this->assertTrue($account->isPersonal());
        $this->assertFalse($account->isShared());
    }

    public function test_account_is_marked_as_shared_when_user_id_is_not_provided(): void
    {
        $this->signIn();
        $payload = EmailAccount::factory()->shared()->raw();

        $account = (new EmailAccountService)->create($payload);

        $this->assertTrue($account->isShared());
        $this->assertFalse($account->isPersonal());
    }

    public function test_from_name_header_is_set_for_personal_email_accounts(): void
    {
        $user = $this->signIn();
        $payload = EmailAccount::factory()->personal($user)->raw();
        $service = new EmailAccountService;
        $account = $service->create(array_merge($payload, [
            'from_name_header' => 'custom from name header',
        ]));

        $this->assertSame('custom from name header', $account->from_name_header);

        $account = $service->update($account, array_merge($payload, [
            'from_name_header' => 'changed custom from name header',
        ]));

        $this->assertSame('changed custom from name header', $account->from_name_header);
    }

    public function test_from_name_header_is_set_for_shared_email_accounts(): void
    {
        $this->signIn();

        $payload = EmailAccount::factory()->shared()->raw();
        $service = new EmailAccountService;

        $account = $service->create(array_merge($payload, [
            'from_name_header' => 'custom from name header',
        ]));

        $this->assertSame('custom from name header', $account->from_name_header);

        $account = $service->update($account, array_merge($payload, [
            'from_name_header' => 'changed custom from name header',
        ]));

        $this->assertSame('changed custom from name header', $account->from_name_header);
    }

    public function test_email_account_folders_are_saved(): void
    {
        $this->signIn();

        $payload = EmailAccount::factory()->raw();
        $service = new EmailAccountService;

        $account = $service->create($payload = array_merge($payload, [
            'folders' => [
                EmailAccountFolder::factory()->raw(['name' => 'INBOX', 'syncable' => true]),
            ],
        ]));

        $this->assertCount(1, $account->folders);

        $payload['folders'] = [
            EmailAccountFolder::factory()->raw(['name' => 'INBOX', 'syncable' => false]),
            EmailAccountFolder::factory()->raw(['name' => 'New Folder']),
        ];

        $account = $service->update($account, $payload);

        $account->load('folders');

        $this->assertSame(2, $account->folders->count());
        $this->assertFalse($account->folders->firstWhere('name', 'INBOX')->syncable);
    }

    public function test_duplicate_folders_are_not_saved(): void
    {
        $this->signIn();
        $payload = EmailAccount::factory()->raw();
        $service = new EmailAccountService;
        $account = $service->create(array_merge($payload, [
            'folders' => [
                EmailAccountFolder::factory()->raw(['name' => 'INBOX']),
                EmailAccountFolder::factory()->raw(['name' => 'INBOX']),
            ],
        ]));

        $this->assertCount(1, $account->folders);

        $payload['folders'] = [
            EmailAccountFolder::factory()->raw(['name' => 'INBOX']),
            EmailAccountFolder::factory()->raw(['name' => 'INBOX']),
        ];

        $account = $service->update($account, $payload);
        $this->assertCount(1, $account->folders);
    }

    public function test_trash_and_sent_folder_are_set_on_create(): void
    {
        $this->signIn();
        $payload = EmailAccount::factory()->raw();

        $account = (new EmailAccountService)->create(array_merge($payload, [
            'folders' => [
                $sent = EmailAccountFolder::factory()->sent()->raw(),
                $trash = EmailAccountFolder::factory()->trash()->raw(),
            ],
        ]));

        $this->assertNotNull($account->trashFolder);
        $this->assertNotNull($account->sentFolder);
        $this->assertSame($trash['name'], $account->trashFolder->name);
        $this->assertSame($sent['name'], $account->sentFolder->name);
    }

    public function test_email_account_folder_can_be_marked_as_not_syncable(): void
    {
        $this->signIn();
        $payload = EmailAccount::factory()->raw();
        $service = new EmailAccountService;

        $account = $service->create(array_merge($payload, [
            'folders' => [
                $folder1 = EmailAccountFolder::factory()->raw(),
                $folder2 = EmailAccountFolder::factory()->raw(['syncable' => false, 'name' => 'SENT']),
            ],
        ]));

        $this->assertCount(1, $account->activeFolders());

        $payload['folders'] = [
            [...$folder1, ...['syncable' => false]],
            $folder2,
        ];

        $account = $service->update($account, $payload);
        $this->assertCount(0, $account->folders()->get()->active());
    }

    public function test_folder_child_folders_are_saved(): void
    {
        $this->signIn();
        $payload = EmailAccount::factory()->raw();
        $parent = EmailAccountFolder::factory()->raw();
        $child = EmailAccountFolder::factory()->sent()->raw(['name' => 'INBOX 1']);
        $child['children'] = [$deepChild = EmailAccountFolder::factory()->sent()->raw(['name' => 'INBOX 2'])];
        $parent['children'] = [$child];

        $account = (new EmailAccountService)->create(array_merge($payload, [
            'folders' => [$parent],
        ]));

        $tree = $account->folders->createTreeFromActive(Request::instance());

        $this->assertCount(3, $account->folders);
        $this->assertCount(1, $tree);
        $this->assertSame($parent['name'], $tree[0]['name']);
        $this->assertSame($child['name'], $tree[0]['children'][0]['name']);
        $this->assertSame($deepChild['name'], $tree[0]['children'][0]['children'][0]['name']);
    }
}
