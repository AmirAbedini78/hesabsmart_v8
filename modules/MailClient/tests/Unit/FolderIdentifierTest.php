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

namespace Modules\MailClient\Tests\Unit;

use Modules\MailClient\Client\FolderIdentifier;
use PHPUnit\Framework\TestCase;

class FolderIdentifierTest extends TestCase
{
    public function test_folder_identifier(): void
    {
        $identifier = new FolderIdentifier('id', 'INBOX');

        $this->assertSame('id', $identifier->key);
        $this->assertSame('INBOX', $identifier->value);
    }
}
