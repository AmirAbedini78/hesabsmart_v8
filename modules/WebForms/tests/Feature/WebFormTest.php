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

namespace Modules\WebForms\Tests\Feature;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Core\Facades\Fields;
use Modules\Core\Fields\Email;
use Modules\Core\Fields\Text;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Deals\Models\Deal;
use Modules\Deals\Models\Pipeline;
use Modules\Users\Models\User;
use Modules\WebForms\Enums\WebFormSection;
use Modules\WebForms\Models\WebForm;
use Tests\TestCase;

class WebFormTest extends TestCase
{
    public function test_when_web_form_created_by_not_provided_uses_current_user_id(): void
    {
        $user = $this->signIn();

        $form = WebForm::factory(['created_by' => null])->create();

        $this->assertEquals($form->created_by, $user->id);
    }

    public function test_web_form_has_light_logo(): void
    {
        config(['core.logo.light' => 'logo.png']);
        $form = WebForm::factory(['styles' => ['logo' => 'light']])->create();

        $this->assertEquals('logo.png', $form->logo());
    }

    public function test_web_form_has_dark_logo(): void
    {
        config(['core.logo.dark' => 'logo.png']);
        $form = WebForm::factory(['styles' => ['logo' => 'dark']])->create();

        $this->assertEquals('logo.png', $form->logo());
    }

    public function test_web_form_created_by_can_be_provided(): void
    {
        $user = $this->createUser();

        $form = WebForm::factory()->for($user, 'creator')->create();

        $this->assertEquals($form->created_by, $user->id);
    }

    public function test_web_form_has_deals(): void
    {
        $form = WebForm::factory()->has(Deal::factory()->count(2))->create();

        $this->assertCount(2, $form->deals);
    }

    public function test_web_form_has_user(): void
    {
        $form = WebForm::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $form->user);
    }

    public function test_web_form_uuid_is_generated(): void
    {
        $form = WebForm::factory(['uuid' => 'uuid-web-form'])->create();

        $this->assertNotEmpty($form->uuid);
    }

    public function test_web_form_has_url_attribute(): void
    {
        $form = WebForm::factory(['uuid' => 'uuid-web-form'])->make();

        $this->assertEquals(route('webform.view', 'uuid-web-form'), $form->publicUrl);
    }

    public function test_web_form_has_status(): void
    {
        $form = WebForm::factory()->make(['status' => 'active']);
        $this->assertEquals('active', $form->status);

        $form = WebForm::factory()->inactive()->make();
        $this->assertEquals('inactive', $form->status);
    }

    public function test_it_can_determine_whether_web_form_is_active(): void
    {
        $form = WebForm::factory()->make(['status' => 'active']);

        $this->assertTrue($form->isActive());
    }

    public function test_it_can_determine_whether_web_form_is_inactive(): void
    {
        $form = WebForm::factory()->inactive()->make();

        $this->assertFalse($form->isActive());
    }

    public function test_web_form_has_fields(): void
    {
        Fields::replace('contacts', [
            Text::make('first_name', 'First Name'),
            Email::make('email', 'Email'),
        ]);

        $form = WebForm::factory()
            ->addFieldSection('email', 'contacts')
            ->addFieldSection('first_name', 'contacts')
            ->make();

        $this->assertCount(2, $form->fields());
    }

    public function test_web_form_fields_does_not_have_help_text(): void
    {
        Fields::replace('contacts', [
            Email::make('email', 'Email')->help('Help Text'),
        ]);

        $form = WebForm::factory()
            ->addFieldSection('email', 'contacts')
            ->make();

        $this->assertEmpty($form->fields()->find('email')->helpText);
    }

    public function test_unique_validated_fields_are_not_unique_on_web_form(): void
    {
        Fields::replace('contacts', [
            Email::make('email', 'Email'),
        ]);

        $form = WebForm::factory()
            ->addFieldSection('email', 'contacts')
            ->make();

        $this->assertFalse($form->fields()->find('email')->isUnique());
    }

    public function test_web_form_fields_can_be_required(): void
    {
        Fields::replace('contacts', [
            Email::make('email', 'Email'),
        ]);

        $form = WebForm::factory()
            ->addFieldSection('email', 'contacts', ['isRequired' => true])
            ->make();

        $this->assertTrue($form->fields()->find('email')->isRequired(app(ResourceRequest::class)));
    }

    public function test_web_form_fields_uses_the_label_provided_from_the_section(): void
    {
        Fields::replace('contacts', [
            Email::make('email', 'Email'),
        ]);

        $form = WebForm::factory()
            ->addFieldSection('email', 'contacts', ['isRequired' => true, 'label' => 'Section Label'])
            ->make();

        $this->assertEquals('Section Label', $form->fields()->find('email')->label);
    }

    public function test_web_form_fields_have_random_request_attribute(): void
    {
        Fields::replace('contacts', [
            Email::make('email', 'Email'),
        ]);

        $form = WebForm::factory()
            ->addFieldSection('email', 'contacts', ['isRequired' => true, 'label' => 'Section Label'])
            ->make();

        $this->assertNotEquals('email', $form->fields()->find('email')->requestAttribute);
        $this->assertNotEmpty('email', $form->fields()->find('email')->requestAttribute);
    }

    public function test_web_form_fields_have_resource_name(): void
    {
        Fields::replace('contacts', [
            Email::make('email', 'Email'),
        ]);

        $form = WebForm::factory()
            ->addFieldSection('email', 'contacts', ['isRequired' => true, 'label' => 'Section Label'])
            ->make();

        $this->assertEquals('contacts', $form->fields()->find('email')->meta()['resourceName']);
    }

    public function test_non_authorized_fields_can_be_used_on_web_forms(): void
    {
        Fields::replace('contacts', [
            Email::make('email', 'Email')->canSee(function () {
                return false;
            }),
        ]);

        $form = WebForm::factory()
            ->addFieldSection('email', 'contacts')
            ->make();

        $this->assertTrue($form->fields()->find('email')->authorizedToSee());
    }

    public function test_it_does_add_the_field_to_the_form_if_the_field_is_removed_from_the_resource(): void
    {
        Fields::replace('contacts', [
            Email::make('email', 'Email'),
            Text::make('first_name', 'First Name'),
        ]);

        $form = WebForm::factory()
            ->addFieldSection('email', 'contacts')
            ->addFieldSection('first_name', 'contacts')
            ->make();

        Fields::replace('contacts', [
            Text::make('first_name', 'First Name'),
        ]);

        $this->assertCount(1, $form->fields());
        $this->assertNull($form->fields()->find('email'));
    }

    public function test_web_form_has_field_sections(): void
    {
        $form = WebForm::factory()->withIntroductionSection()
            ->addFieldSection('email')
            ->addFieldSection('first_name')
            ->make();

        $this->assertCount(2, $form->fieldSections());
    }

    public function test_web_form_has_file_sections(): void
    {
        $form = WebForm::factory()->withIntroductionSection()
            ->addFileSection()
            ->addFileSection()
            ->make();

        $this->assertCount(2, $form->fileSections());
    }

    public function test_it_can_find_web_form_section_by_type(): void
    {
        $form = WebForm::factory()->withSubmitButtonSection()->make();
        $this->assertNotEmpty($form->sections(WebFormSection::SUBMIT));
    }

    public function test_web_form_has_submit_section(): void
    {
        $form = WebForm::factory()->withSubmitButtonSection(['text' => 'Submit'])->make();

        $this->assertEquals('Submit', $form->submitSection()['text']);
        $this->assertEquals(WebFormSection::SUBMIT->value, $form->submitSection()['type']);
    }

    public function test_web_form_has_introduction_section(): void
    {
        $form = WebForm::factory()->withIntroductionSection([
            'title' => 'Testing',
            'message' => 'Testing Message',
        ])->make();

        $this->assertEquals('Testing', $form->introductionSection()['title']);
        $this->assertEquals('Testing Message', $form->introductionSection()['message']);
        $this->assertEquals(WebFormSection::INTRODUCTION->value, $form->introductionSection()['type']);
    }

    public function test_web_form_submit_data_attribute_returns_defaults_when_has_no_submit_data(): void
    {
        $pipeline = Pipeline::factory()->withStages()->primary()->create();
        $firstStageId = $pipeline->stages->first()->getKey();

        $form = WebForm::factory(['submit_data' => []])->make();

        $this->assertEquals([
            'pipeline_id' => $pipeline->id,
            'stage_id' => $firstStageId,
        ], $form->submit_data);
    }

    public function test_web_form_submit_data_attribute_returns_default_pipeline_when_the_pipeline_is_deleted(): void
    {
        $primary = Pipeline::factory()->withStages()->primary()->create();
        $primaryFirstStage = $primary->stages->first()->getKey();

        $pipeline = Pipeline::factory()->withStages()->create();
        $firstStageId = $pipeline->stages->first()->getKey();

        $form = WebForm::factory(['submit_data' => [
            'pipeline_id' => $pipeline->id,
            'stage_id' => $firstStageId,
        ]])->make();

        $pipeline->stages()->delete();
        $pipeline->delete();

        $this->assertEquals([
            'pipeline_id' => $primary->id,
            'stage_id' => $primaryFirstStage,
        ], $form->submit_data);
    }

    public function test_web_form_submit_data_attribute_returns_first_stage_from_pipeline_when_the_stage_is_deleted(): void
    {
        $pipeline = Pipeline::factory()->withStages()->create();
        $firstStage = $pipeline->stages->first();

        $form = WebForm::factory(['submit_data' => [
            'pipeline_id' => $pipeline->id,
            'stage_id' => $firstStage->id,
        ]])->make();

        $firstStage->delete();

        $this->assertEquals([
            'pipeline_id' => $pipeline->id,
            'stage_id' => $pipeline->stages()->get()->first()->id,
        ], $form->submit_data);
    }

    public function test_it_can_find_form_field_by_resource(): void
    {
        $form = WebForm::factory()->addFieldSection('email', 'contacts')->make();

        $this->assertNotNull($form->fieldByResource('email', 'contacts'));
    }

    public function test_it_can_find_web_form_by_uuid(): void
    {
        $form = WebForm::factory()->create();

        $this->assertInstanceOf(WebForm::class, WebForm::findByUuid($form->uuid));
    }

    public function test_it_fails_when_web_form_by_uuid_is_not_found(): void
    {
        try {
            WebForm::findByUuid('fake');
            $this->assertFalse(false, 'It does not fail when web form by uuid is not found.');
        } catch (ModelNotFoundException) {
            $this->assertTrue(true);
        }
    }
}
