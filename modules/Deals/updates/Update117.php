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
        ToModuleMigrator::make('deals')
            ->migrateMorphs('App\\Models\\Deal', 'Modules\\Deals\\Models\\Deal')
            ->migrateMailableTemplates([
                'App\\Mail\\UserAssignedToDeal' => 'Modules\Deals\Mail\UserAssignedToDeal',
            ])
            ->migrateNotifications([
                'App\\Notifications\\UserAssignedToDeal' => 'Modules\Deals\Notifications\UserAssignedToDeal',
            ])
            ->migrateDbLanguageKeys('deal')
            ->migrateLanguageFiles(['deal.php', 'board.php'])
            ->migrateWorkflowTriggers([
                'App\\Workflows\\Triggers\\DealCreated' => 'Modules\Deals\Workflow\Triggers\DealCreated',
                'App\\Workflows\\Triggers\\DealStageChanged' => 'Modules\Deals\Workflow\Triggers\DealStageChanged',
                'App\\Workflows\\Triggers\\DealStatusChanged' => 'Modules\Deals\Workflow\Triggers\DealStatusChanged',
            ])
            ->migrateWorkflowActions([
                'App\\Workflows\\Triggers\\MarkAssociatedDealsAsLost' => 'Modules\Deals\Workflow\Triggers\MarkAssociatedDealsAsLost',
                'App\\Workflows\\Triggers\\MarkAssociatedDealsAsWon' => 'Modules\Deals\Workflow\Triggers\MarkAssociatedDealsAsWon',
            ])
            ->deleteConflictedFiles($this->getConflictedFiles());
    }

    public function shouldRun(): bool
    {
        return file_exists(app_path('Models/Deal.php'));
    }

    protected function getConflictedFiles(): array
    {
        return [
            app_path('Resources/Deal'),

            app_path('Mail/UserAssignedToDeal.php'),
            app_path('Mail/UserAssignedToDeal.php'),

            app_path('Notifications/UserAssignedToDeal.php'),
            app_path('Notifications/UserAssignedToDeal.php'),

            app_path('Workflows/Triggers/DealCreated.php'),
            app_path('Workflows/Triggers/DealStageChanged.php'),
            app_path('Workflows/Triggers/DealStatusChanged.php'),

            app_path('Workflows/Actions/MarkAssociatedDealsAsLost.php'),
            app_path('Workflows/Actions/MarkAssociatedDealsAsWon.php'),

            app_path('Models/Deal.php'),
            app_path('Models/Stage.php'),
            app_path('Models/Pipeline.php'),
            app_path('Models/LostReason.php'),
            app_path('Models/StageHistory.php'),
        ];
    }
};
