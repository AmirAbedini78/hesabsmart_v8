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

use Modules\Contacts\Http\Resources\SourceResource;
use Modules\Contacts\Models\Source as SourceModel;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\BelongsTo;

class Source extends BelongsTo
{
    /**
     * Create new instance of Source field
     *
     * @param  string  $label  Custom label
     */
    public function __construct($label = null)
    {
        parent::__construct('source', SourceModel::class, $label ?? __('contacts::source.source'));

        $this->setJsonResource(SourceResource::class)
            ->options(Innoclapps::resourceByModel(SourceModel::class))
            ->acceptLabelAsValue();
    }
}
