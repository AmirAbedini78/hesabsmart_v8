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

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Tool;

class ExecuteTool extends ApiController
{
    /**
     * Execute the given tool.
     */
    public function __invoke(string $name): JsonResponse
    {
        // Tool execute flag

        $tool = Innoclapps::getTools()->first(fn (Tool $tool) => $tool->getName() == $name);

        abort_unless($tool, 404);

        $data = $tool->execute();

        return $this->response(
            $data,
            is_string($data) && empty($data) || is_null($data) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK
        );
    }
}
