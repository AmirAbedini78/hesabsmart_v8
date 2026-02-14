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

namespace Modules\Calls\Tests\Feature;

use Modules\Calls\Models\Call;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Contact;
use Tests\TestCase;

class HasCallsTest extends TestCase
{
    public function test_it_does_not_detach_calls_when_associated_model_is_soft_deleted(): void
    {
        $contact = Contact::factory()->create();

        $call = Call::factory()->hasAttached($contact)->create();

        $contact->delete();

        $this->assertEquals(1, $call->contacts()->withTrashed()->count());
    }

    public function test_it_detaches_calls_when_associated_model_deleted(): void
    {
        $contact = Contact::factory()->create();

        $call = Call::factory()
            ->hasAttached(Company::factory())
            ->hasAttached($contact)->create();

        $contact->forceDelete();

        $this->assertCount(0, $call->contacts);
        $this->assertCount(1, $call->companies);
    }

    public function test_call_with_multiple_associations_is_not_deleted_when_deleting_associated_model(): void
    {
        $call = Call::factory()
            ->hasAttached($contact = Contact::factory()->create())
            ->hasAttached(Company::factory())
            ->hasAttached(Contact::factory())->create();

        $contact->forceDelete();

        $this->assertModelExists($call);
        $this->assertEquals(1, $call->contacts->count());
        $this->assertEquals(1, $call->companies->count());
    }

    public function test_call_is_deleted_when_deleting_last_associated_model(): void
    {
        $call = Call::factory()->hasAttached($contact = Contact::factory()->create())->create();

        $contact->forceDelete();

        $this->assertModelMissing($call);
    }
}
