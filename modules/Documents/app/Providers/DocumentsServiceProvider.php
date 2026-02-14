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

namespace Modules\Documents\Providers;

use Closure;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Pages\Tab;
use Modules\Core\Settings\DefaultSettings;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Core\Workflow\Workflows;
use Modules\Documents\Console\Commands\SendScheduledDocuments;
use Modules\Documents\Content\DocumentContent;
use Modules\Documents\Enums\DocumentStatus;
use Modules\Documents\Http\Resources\DocumentTypeResource;
use Modules\Documents\Listeners\TransferDocumentsUserData;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentType;
use Modules\Users\Events\TransferringUserData;

class DocumentsServiceProvider extends ModuleServiceProvider
{
    protected bool $withViews = true;

    protected array $resources = [
        \Modules\Documents\Resources\Document::class,
        \Modules\Documents\Resources\DocumentType::class,
        \Modules\Documents\Resources\DocumentTemplate::class,
    ];

    protected array $notifications = [
        \Modules\Documents\Notifications\DocumentAccepted::class,
        \Modules\Documents\Notifications\DocumentViewed::class,
        \Modules\Documents\Notifications\SignerSignedDocument::class,
        \Modules\Documents\Notifications\UserAssignedToDocument::class,
    ];

    protected array $mailableTemplates = [
        \Modules\Documents\Mail\DocumentAccepted::class,
        \Modules\Documents\Mail\DocumentViewed::class,
        \Modules\Documents\Mail\SignerSignedDocument::class,
        \Modules\Documents\Mail\UserAssignedToDocument::class,
    ];

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(TransferringUserData::class, TransferDocumentsUserData::class);

        $this->commands([
            SendScheduledDocuments::class,
        ]);
    }

    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Configure the module.
     */
    protected function setup(): void
    {
        $this->registerWorkflowTriggers();
        $this->registerRelatedRecordsDetailTab();

        DatabaseState::register(\Modules\Documents\Database\State\EnsureDocumentTypesArePresent::class);

        DefaultSettings::addRequired('default_document_type');
    }

    /**
     * Register the documents module available workflows.
     */
    protected function registerWorkflowTriggers(): void
    {
        Workflows::triggers([
            \Modules\Documents\Workflow\Triggers\DocumentStatusChanged::class,
        ]);
    }

    /**
     * Register the documents module related tabs.
     */
    protected function registerRelatedRecordsDetailTab(): void
    {
        $tab = Tab::make('documents', 'documents-tab')->panel('documents-tab-panel')->order(25);

        foreach (['contacts', 'companies', 'deals'] as $resourceName) {
            if ($resource = Innoclapps::resourceByName($resourceName)) {
                $resource->getDetailPage()->tab($tab);
            }
        }
    }

    /**
     * Schedule module tasks.
     */
    protected function scheduleTasks(Schedule $schedule): void
    {
        $schedule->safeCommand('documents:send-scheduled')
            ->name('send-scheduled-documents')
            ->everyTwoMinutes()
            ->withoutOverlapping(5);
    }

    /**
     * Provide the data to share on the front-end.
     */
    protected function scriptData(): Closure
    {
        return fn () => Auth::check() ? [
            'documents' => [
                'default_document_type' => DocumentType::getDefaultType(),

                'navigation_heading_tag_name' => DocumentContent::NAVIGATION_HEADING_TAG_NAME,

                'placeholders' => (new Document)->placeholders(),

                'statuses' => collect(DocumentStatus::cases())->mapWithKeys(
                    function (DocumentStatus $case) {
                        return [
                            $case->value => [
                                'name' => $case->value,
                                'icon' => $case->icon(),
                                'color' => $case->color(),
                                'display_name' => $case->displayName(),
                            ],
                        ];
                    }
                ),

                'types' => DocumentTypeResource::collection(
                    DocumentType::withCommon()
                        ->withVisibilityGroups()
                        ->visible()
                        ->orderBy('name')
                        ->get()
                ),
            ],
        ] : [];
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Documents';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'documents';
    }
}
