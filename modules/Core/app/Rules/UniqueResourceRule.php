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

namespace Modules\Core\Rules;

class UniqueResourceRule extends UniqueRule
{
    /**
     * Indicates whether to exclude the unique validation from import.
     */
    public bool $skipOnImport = false;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $modelName, string|int|null $ignore = 'resourceId', ?string $column = 'NULL')
    {
        parent::__construct($modelName, $ignore, $column);
    }

    /**
     * Set whether the exclude this validation rule from import.
     */
    public function skipOnImport(bool $value): static
    {
        $this->skipOnImport = $value;

        return $this;
    }
}
