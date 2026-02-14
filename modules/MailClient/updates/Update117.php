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

use App\ToModuleMigrator;
use Modules\Updater\UpdatePatcher;

return new class extends UpdatePatcher
{
    public function run(): void
    {
        ToModuleMigrator::make('mailclient')
            ->migrateMorphs('App\\Models\EmailAccount', 'Modules\\MailClient\\Models\\EmailAccount')
            ->migrateMorphs('App\\Models\EmailAccountFolder', 'Modules\\MailClient\\Models\\EmailAccountFolder')
            ->migrateMorphs('App\\Models\EmailAccountMessage', 'Modules\\MailClient\\Models\\EmailAccountMessage')
            ->migrateMorphs('App\\Models\EmailAccountMessageAddress', 'Modules\\MailClient\\Models\\EmailAccountMessageAddress')
            ->migrateMorphs('App\\Models\EmailAccountMessageFolder', 'Modules\\MailClient\\Models\\EmailAccountMessageFolder')
            ->migrateMorphs('App\\Models\EmailAccountMessageHeader', 'Modules\\MailClient\\Models\\EmailAccountMessageHeader')
            ->migrateDbLanguageKeys('inbox')
            ->migrateDbLanguageKeys('mail')
            ->migrateLanguageFiles(['inbox.php', 'mail.php'])
            ->deleteConflictedFiles($this->getConflictedFiles());
    }

    public function shouldRun(): bool
    {
        return file_exists(app_path('Models/EmailAccountMessage.php'));
    }

    protected function getConflictedFiles(): array
    {
        return [
            app_path('Resources/Inbox'),
            app_path('Models/EmailAccountMessage.php'),
            app_path('Models/EmailAccount.php'),
        ];
    }
};
