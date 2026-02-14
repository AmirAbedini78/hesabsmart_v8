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

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Import;
use Modules\Users\Models\User;

class ImportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Import::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_path' => 'fake/path/file.csv',
            'resource_name' => 'contacts',
            'status' => 'mapping',
            'imported' => 0,
            'skipped' => 0,
            'duplicates' => 0,
            'user_id' => User::factory(),
            'completed_at' => null,
            'data' => ['mappings' => []],
        ];
    }
}
