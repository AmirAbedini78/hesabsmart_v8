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

use Illuminate\Support\Facades\Lang;
use Modules\MailClient\Client\FolderIdentifier;
use Modules\MailClient\Models\EmailAccount;
use Modules\MailClient\Models\EmailAccountFolder;
use Modules\MailClient\Support\EmailAccountFolderCollection;
use Tests\TestCase;

class EmailAccountFolderModelTest extends TestCase
{
    public function test_folder_has_account(): void
    {
        $folder = EmailAccountFolder::factory()->for(EmailAccount::factory(), 'account')->create();

        $this->assertInstanceOf(EmailAccount::class, $folder->account);
    }

    public function test_folder_has_identifier(): void
    {
        $folder = EmailAccountFolder::factory()->create();

        $this->assertInstanceOf(FolderIdentifier::class, $folder->identifier());
    }

    public function test_folder_identifier_uses_name_when_account_is_of_type_imap(): void
    {
        $folder = EmailAccountFolder::factory()->for(EmailAccount::factory()->imap(), 'account')->create();

        $this->assertEquals($folder->name, $folder->identifier()->value);
        $this->assertEquals('name', $folder->identifier()->key);
    }

    public function test_folder_identifier_uses_id_when_account_is_not_of_type_imap(): void
    {
        $folder = EmailAccountFolder::factory()->for(EmailAccount::factory()->gmail(), 'account')->create();

        $this->assertEquals($folder->remote_id, $folder->identifier()->value);
        $this->assertEquals('id', $folder->identifier()->key);

        $folder = EmailAccountFolder::factory()->for(EmailAccount::factory()->outlook(), 'account')->create();

        $this->assertEquals($folder->remote_id, $folder->identifier()->value);
        $this->assertEquals('id', $folder->identifier()->key);
    }

    public function test_folder_has_display_name(): void
    {
        $folder = EmailAccountFolder::factory()->create(['display_name' => 'TEST DISPLAY NAME']);

        $this->assertSame('TEST DISPLAY NAME', $folder->display_name);
    }

    public function test_folder_display_name_can_be_custom_translated(): void
    {
        Lang::addLines([
            'custom.mail.labels.INBOX-TEST' => 'Custom folder name',
        ], 'en');

        $folder = EmailAccountFolder::factory()->create(['display_name' => 'INBOX-TEST']);

        $this->assertEquals('Custom folder name', $folder->display_name);
    }

    public function test_when_exists_it_uses_the_predefined_display_name_from_language_file(): void
    {
        Lang::addLines([
            'custom.mail.labels.INBOX-TEST' => 'Custom folder name',
        ], 'en');

        $folder = EmailAccountFolder::factory()->create(['display_name' => 'INBOX-TEST']);

        $this->assertEquals('Custom folder name', $folder->display_name);
    }

    public function test_it_does_not_use_predefined_display_name_if_custom_display_name_is_added(): void
    {
        Lang::addLines([
            'mailclient::mail.labels.INBOX-TEST' => 'Custom folder name',
            'custom.mail.labels.INBOX-TEST' => 'Custom prioritized name',
        ], 'en');

        $folder = EmailAccountFolder::factory()->create(['display_name' => 'INBOX-TEST']);

        $this->assertEquals('Custom prioritized name', $folder->display_name);
    }

    public function test_folder_uses_custom_collection(): void
    {
        $folder = new EmailAccountFolder;

        $this->assertInstanceOf(EmailAccountFolderCollection::class, $folder->newCollection());
    }
}
