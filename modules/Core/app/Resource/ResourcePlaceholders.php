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

namespace Modules\Core\Resource;

use Illuminate\Support\Arr;
use Modules\Core\Common\Placeholders\Placeholders as BasePlaceholders;
use Modules\Core\Common\Placeholders\UrlPlaceholder;
use Modules\Core\Models\Model;
use Modules\Core\Support\Makeable;

class ResourcePlaceholders extends BasePlaceholders
{
    use Makeable;

    /**
     * The groups of placeholders.
     *
     * Supports multiple groups because of Mustache parsing, if parsing one by one, non-existent vars are left empty.
     */
    protected array $groups = [];

    /**
     * Initialze new Placeholders instance
     *
     * @param  \Modules\Core\Models\Model|null  $model  Provide the model when parsing is needed
     */
    public function __construct(PlaceholdersGroup|Resource|array $resources, protected ?Model $model = null)
    {
        foreach (Arr::wrap($resources) as $resource) {
            $this->groups[] = $resource instanceof Resource ?
                new PlaceholdersGroup($resource, $model) :
                $resource;
        }

        parent::__construct([]);

        $this->setPlaceholdersFromResources();
    }

    /**
     * Push an URL placeholder to the placeholders array.
     */
    public function withUrlPlaceholder(string $tag = 'url'): static
    {
        foreach ($this->groups as $group) {
            $this->push(
                UrlPlaceholder::make($group->getModel(), $tag)
                    ->prefixTag($group->tagPrefix())
                    ->description($group->getResource()->singularLabel().' URL')
            );
        }

        return $this;
    }

    /**
     * Create placeholders groups for edit as fields from the given resources
     */
    public static function createGroupsFromResources(array $resources): array
    {
        return collect($resources)->mapWithKeys(function (Resource|string $resource) {
            return with(new PlaceholdersGroup($resource), fn (PlaceholdersGroup $group) => [$resource => [
                'label' => $group->getResource()->singularLabel(),
                'placeholders' => $group->all(),
            ]]);
        })->reject(fn (array $group) => empty($group['placeholders']))->all();
    }

    /**
     * Set the placeholders from the current resources.
     */
    protected function setPlaceholdersFromResources(): void
    {
        foreach ($this->groups as $group) {
            $this->push($group->all());
        }
    }
}
