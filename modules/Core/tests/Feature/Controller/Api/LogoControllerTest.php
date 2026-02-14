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

namespace Modules\Core\Tests\Feature\Controller\Api;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LogoControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_logo_endpoints(): void
    {
        $this->postJson('/api/logo/dark')->assertUnauthorized();
        $this->deleteJson('/api/logo/dark')->assertUnauthorized();
        $this->postJson('/api/logo/light')->assertUnauthorized();
        $this->deleteJson('/api/logo/light')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_logo_endpoints(): void
    {
        $this->asRegularUser()->signIn();

        $this->postJson('/api/logo/dark')->assertForbidden();
        $this->deleteJson('/api/logo/dark')->assertForbidden();
        $this->postJson('/api/logo/light')->assertForbidden();
        $this->deleteJson('/api/logo/light')->assertForbidden();
    }

    public function test_user_can_upload_logo(): void
    {
        $this->signIn();

        Storage::fake('public');

        foreach (['light', 'dark'] as $type) {
            $logo = $this->postJson('/api/logo/'.$type, [
                'logo_'.$type => UploadedFile::fake()->image('logo_'.$type.'.jpg'),
            ])->getData()->logo;

            $fileName = basename($logo);

            $this->assertEquals(settings()->get('logo_'.$type), $logo);
            Storage::disk('public')->assertExists($fileName);
        }
    }

    public function test_user_can_delete_logo(): void
    {
        $this->signIn();

        Storage::fake('public');

        foreach (['light', 'dark'] as $type) {
            $response = $this->postJson('/api/logo/'.$type, [
                'logo_'.$type => UploadedFile::fake()->image('logo_'.$type.'.jpg'),
            ]);

            $fileName = basename($response->getData()->logo);

            $this->deleteJson('/api/logo/'.$type);

            $this->assertEmpty(settings()->get('logo_'.$type));
            Storage::disk('public')->assertMissing($fileName);
        }
    }
}
