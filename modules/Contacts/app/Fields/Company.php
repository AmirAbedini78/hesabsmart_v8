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

namespace Modules\Contacts\Fields;

use Modules\Contacts\Http\Resources\CompanyResource;
use Modules\Contacts\Models\Company as CompanyModel;
use Modules\Core\Fields\BelongsTo;

class Company extends BelongsTo
{
    /**
     * Create new instance of Company field
     *
     * @param  string  $relationName  The relation name, snake case format
     * @param  string  $label  Custom label
     * @param  string  $foreignKey  Custom foreign key
     */
    public function __construct($relationName = 'company', $label = null, $foreignKey = null)
    {
        parent::__construct($relationName, CompanyModel::class, $label ?? __('contacts::company.company'), $foreignKey);

        $this->setJsonResource(CompanyResource::class)
            ->lazyLoad('/companies', ['order' => 'created_at|desc'])
            ->onOptionClick('float', ['resourceName' => 'companies'])
            ->async('/companies/search');
    }
}
