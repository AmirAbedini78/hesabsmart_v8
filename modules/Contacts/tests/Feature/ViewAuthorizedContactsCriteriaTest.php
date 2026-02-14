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

namespace Modules\Contacts\Tests\Feature;

use Modules\Contacts\Criteria\ViewAuthorizedContactsCriteria;
use Modules\Contacts\Models\Contact;
use Tests\TestCase;

class ViewAuthorizedContactsCriteriaTest extends TestCase
{
    public function test_own_contacts_criteria_queries_only_own_contacts(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view own contacts')->createUser();

        Contact::factory()->for($user)->create();
        Contact::factory()->create();

        $this->signIn($user);

        $query = Contact::criteria(ViewAuthorizedContactsCriteria::class);

        $this->assertSame(1, $query->count());
    }

    public function test_it_returns_all_contacts_when_user_is_authorized_to_see_all_contacts(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view all contacts')->createUser();

        Contact::factory()->for($user)->create();
        Contact::factory()->create();

        $this->signIn($user);

        $query = Contact::criteria(ViewAuthorizedContactsCriteria::class);

        $this->assertSame(2, $query->count());

        $this->signIn();
        $this->assertSame(2, $query->count());
    }
}
