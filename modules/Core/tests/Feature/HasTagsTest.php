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

namespace Modules\Core\Tests\Feature;

use Modules\Contacts\Models\Contact;
use Modules\Core\Models\Tag;
use Tests\TestCase;

class HasTagsTest extends TestCase
{
    public function test_attach_and_detach_tags(): void
    {
        $model = Contact::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        // Attach tags
        $model->attachTags([$tag1, $tag2]);

        $this->assertCount(2, $model->tags);

        // Detach a tag
        $model->detachTag($tag1);

        $model->refresh();

        $this->assertCount(1, $model->tags);
        $this->assertTrue($model->tags->contains($tag2));
        $this->assertFalse($model->tags->contains($tag1));
    }

    public function test_sync_tags(): void
    {
        $model = Contact::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        // Sync tags
        $model->syncTags([$tag1, $tag2]);

        $this->assertCount(2, $model->tags);

        $tag3 = Tag::factory()->create();

        // Sync new tags
        $model->syncTags([$tag3]);

        $model->refresh();
        $this->assertCount(1, $model->tags);
        $this->assertTrue($model->tags->contains($tag3));
        $this->assertFalse($model->tags->contains($tag1));
        $this->assertFalse($model->tags->contains($tag2));
    }

    public function test_scope_with_all_tags(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        $model1 = Contact::factory()->create();
        $model1->attachTags([$tag1, $tag2]);

        $model2 = Contact::factory()->create();
        $model2->attachTags([$tag1]);

        $model3 = Contact::factory()->create();
        $model3->attachTags([$tag2]);

        $models = Contact::withAllTags([$tag1, $tag2])->get();

        $this->assertCount(1, $models);
        $this->assertTrue($models->contains($model1));
        $this->assertFalse($models->contains($model2));
        $this->assertFalse($models->contains($model3));
    }

    public function test_scope_with_any_tags(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        $model1 = Contact::factory()->create();
        $model1->attachTags([$tag1, $tag2]);

        $model2 = Contact::factory()->create();
        $model2->attachTags([$tag1]);

        $model3 = Contact::factory()->create();
        $model3->attachTags([$tag3]);

        $models = Contact::withAnyTags([$tag1, $tag3])->get();

        $this->assertCount(3, $models);
        $this->assertTrue($models->contains($model1));
        $this->assertTrue($models->contains($model2));
        $this->assertTrue($models->contains($model3));
    }

    public function test_scope_without_tags(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $model1 = Contact::factory()->create();
        $model1->attachTags([$tag1]);

        $model2 = Contact::factory()->create();
        $model2->attachTags([$tag2]);

        $model3 = Contact::factory()->create();

        $models = Contact::withoutTags([$tag1, $tag2])->get();

        $this->assertCount(1, $models);
        $this->assertTrue($models->contains($model3));
    }

    public function test_scope_with_all_tags_of_any_type(): void
    {
        $tag1 = Tag::factory()->create(['type' => 'type1']);
        $tag2 = Tag::factory()->create(['type' => 'type2']);
        $tag3 = Tag::factory()->create(['type' => 'type1']);

        $model1 = Contact::factory()->create();
        $model1->attachTags([$tag1, $tag2]);

        $model2 = Contact::factory()->create();
        $model2->attachTags([$tag1]);

        $model3 = Contact::factory()->create();
        $model3->attachTags([$tag2]);

        $models = Contact::withAllTagsOfAnyType([$tag1, $tag2])->get();

        $this->assertCount(1, $models);
        $this->assertTrue($models->contains($model1));
        $this->assertFalse($models->contains($model2));
        $this->assertFalse($models->contains($model3));
    }

    public function test_scope_with_any_tags_of_any_type(): void
    {
        $tag1 = Tag::factory()->create(['type' => 'type1']);
        $tag2 = Tag::factory()->create(['type' => 'type2']);
        $tag3 = Tag::factory()->create(['type' => 'type1']);

        $model1 = Contact::factory()->create();
        $model1->attachTags([$tag1, $tag2]);

        $model2 = Contact::factory()->create();
        $model2->attachTags([$tag1]);

        $model3 = Contact::factory()->create();
        $model3->attachTags([$tag3]);

        $models = Contact::withAnyTagsOfAnyType([$tag1, $tag3])->get();

        $this->assertCount(3, $models);
        $this->assertTrue($models->contains($model1));
        $this->assertTrue($models->contains($model2));
        $this->assertTrue($models->contains($model3));
    }

    public function test_tags_with_type(): void
    {
        $model = Contact::factory()->create();
        $tag1 = Tag::factory()->create(['type' => 'type1']);
        $tag2 = Tag::factory()->create(['type' => 'type2']);

        $model->attachTags([$tag1, $tag2]);

        $tagsOfType1 = $model->tagsWithType('type1');
        $tagsOfType2 = $model->tagsWithType('type2');

        $this->assertCount(1, $tagsOfType1);
        $this->assertTrue($tagsOfType1->contains($tag1));
        $this->assertFalse($tagsOfType1->contains($tag2));

        $this->assertCount(1, $tagsOfType2);
        $this->assertTrue($tagsOfType2->contains($tag2));
        $this->assertFalse($tagsOfType2->contains($tag1));
    }

    public function test_attach_tag(): void
    {
        $model = Contact::factory()->create();
        $tag = Tag::factory()->create(['type' => 'type1']);

        $model->attachTag($tag);

        $this->assertCount(1, $model->tags);
        $this->assertTrue($model->tags->contains($tag));
    }

    public function test_sync_tags_with_type(): void
    {
        $model = Contact::factory()->create();
        $tag1 = Tag::factory()->create(['type' => 'type1']);
        $tag2 = Tag::factory()->create(['type' => 'type1']);
        $tag3 = Tag::factory()->create(['type' => 'type2']);

        // Sync tags of type1
        $model->syncTagsWithType([$tag1, $tag2], 'type1');

        $this->assertCount(2, $model->tags);
        $this->assertTrue($model->tags->contains($tag1));
        $this->assertTrue($model->tags->contains($tag2));

        // Sync tags of type2
        $model->syncTagsWithType([$tag3], 'type2');

        $model->refresh();
        $this->assertCount(3, $model->tags);
        $this->assertTrue($model->tags->contains($tag1));
        $this->assertTrue($model->tags->contains($tag2));
        $this->assertTrue($model->tags->contains($tag3));
    }
}
