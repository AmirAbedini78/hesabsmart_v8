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

namespace Modules\Deals\Providers;

use Closure;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Facades\Menu;
use Modules\Core\Settings\DefaultSettings;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Core\Workflow\Workflows;
use Modules\Deals\Events\DealMovedToStage;
use Modules\Deals\Http\Resources\LostReasonResource;
use Modules\Deals\Http\Resources\PipelineResource;
use Modules\Deals\Listeners\LogDealMovedToStageActivity;
use Modules\Deals\Listeners\TransferDealsUserData;
use Modules\Deals\Menu\OpenDealsMetric;
use Modules\Deals\Models\Deal;
use Modules\Deals\Models\LostReason;
use Modules\Deals\Models\Pipeline;
use Modules\Users\Events\TransferringUserData;

class DealsServiceProvider extends ModuleServiceProvider
{
    protected array $resources = [
        \Modules\Deals\Resources\Deal::class,
        \Modules\Deals\Resources\Pipeline::class,
        \Modules\Deals\Resources\PipelineStage::class,
        \Modules\Deals\Resources\LostReason::class,
    ];

    protected array $mailableTemplates = [
        \Modules\Deals\Mail\UserAssignedToDeal::class,
    ];

    protected array $notifications = [
        \Modules\Deals\Notifications\UserAssignedToDeal::class,
    ];

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(DealMovedToStage::class, LogDealMovedToStageActivity::class);
        $this->app['events']->listen(TransferringUserData::class, TransferDealsUserData::class);
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

        DatabaseState::register([
            \Modules\Deals\Database\State\EnsureDefaultPipelineIsPresent::class,
        ]);

        DefaultSettings::add('allow_lost_reason_enter', true);
        DefaultSettings::add('lost_reason_is_required', true);

        Menu::metric(new OpenDealsMetric);
    }

    /**
     * Register the documents module available workflows.
     */
    protected function registerWorkflowTriggers(): void
    {
        Workflows::triggers([
            \Modules\Deals\Workflow\Triggers\DealCreated::class,
            \Modules\Deals\Workflow\Triggers\DealStageChanged::class,
            \Modules\Deals\Workflow\Triggers\DealStatusChanged::class,
        ]);
    }

    /**
     * Provide the data to share on the front-end.
     */
    protected function scriptData(): Closure
    {
        return fn () => Auth::check() ? [
            'allow_lost_reason_enter' => settings('allow_lost_reason_enter'),
            'lost_reason_is_required' => settings('lost_reason_is_required'),
            'deals' => [
                'tags_type' => Deal::TAGS_TYPE,
                'pipelines' => PipelineResource::collection(
                    Pipeline::withCommon()
                        ->with('stages')
                        ->withVisibilityGroups()
                        ->visible()
                        ->orderByUserSpecified(Auth::user())
                        ->get()
                ),
                'lost_reasons' => LostReasonResource::collection(
                    LostReason::withCommon()->orderBy('name')->get()
                ),
            ],
        ] : [];
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Deals';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'deals';
    }
}
