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

namespace Modules\Contacts\Providers;

use Modules\Contacts\Listeners\AttachEmailAccountMessageToContact;
use Modules\Contacts\Listeners\CreateContactFromEmailAccountMessage;
use Modules\Contacts\Listeners\TransferContactsUserData;
use Modules\Contacts\Listeners\UpdateLastContactedDate;
use Modules\Contacts\Models\Contact;
use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Settings\DefaultSettings;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Core\Workflow\Workflows;
use Modules\MailClient\Client\Events\MessageSent;
use Modules\MailClient\Events\EmailAccountMessageCreated;
use Modules\Users\Events\TransferringUserData;

class ContactsServiceProvider extends ModuleServiceProvider
{
    protected array $resources = [
        \Modules\Contacts\Resources\Company::class,
        \Modules\Contacts\Resources\Contact::class,
        \Modules\Contacts\Resources\Source::class,
        \Modules\Contacts\Resources\Industry::class,
    ];

    protected array $notifications = [
        \Modules\Contacts\Notifications\UserAssignedToCompany::class,
        \Modules\Contacts\Notifications\UserAssignedToContact::class,
    ];

    protected array $mailableTemplates = [
        \Modules\Contacts\Mail\UserAssignedToCompany::class,
        \Modules\Contacts\Mail\UserAssignedToContact::class,
    ];

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(EmailAccountMessageCreated::class, CreateContactFromEmailAccountMessage::class);
        $this->app['events']->listen(EmailAccountMessageCreated::class, AttachEmailAccountMessageToContact::class);
        $this->app['events']->listen([MessageSent::class, 'eloquent.created: Modules\Calls\Models\Call'], UpdateLastContactedDate::class);
        $this->app['events']->listen(TransferringUserData::class, TransferContactsUserData::class);
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
            \Modules\Contacts\Database\State\EnsureIndustriesArePresent::class,
            \Modules\Contacts\Database\State\EnsureSourcesArePresent::class,
            \Modules\Contacts\Database\State\EnsureDefaultContactTagsArePresent::class,
        ]);

        DefaultSettings::add('require_calling_prefix_on_phones', true);
        DefaultSettings::add('auto_associate_company_to_contact', 1);
    }

    /**
     * Register the documents module available workflows.
     */
    protected function registerWorkflowTriggers(): void
    {
        Workflows::triggers([
            \Modules\Contacts\Workflow\Triggers\CompanyCreated::class,
            \Modules\Contacts\Workflow\Triggers\ContactCreated::class,
        ]);
    }

    /**
     * Provide the data to share on the front-end.
     */
    protected function scriptData(): array
    {
        return [
            'contacts' => [
                'tags_type' => Contact::TAGS_TYPE,
            ],
        ];
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Contacts';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'contacts';
    }
}
