<?php
/**
 * Copyright optile GmbH 2013
 * Licensed under the Software License Agreement in effect between optile and
 * Licensee/user (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 * http://www.optile.de/software-license-agreement; in addition, a countersigned
 * copy has been provided to you for your records. Unless required by applicable
 * law or agreed to in writing or otherwise stipulated in the License, software
 * distributed under the License is distributed on an "as is” basis without
 * warranties or conditions of any kind, either express or implied.  See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * @author      i-Ways <dev@i-ways.hr>
 * @copyright   Copyright (c) 2013 optile GmbH. (http://www.optile.de)
 * @license     http://www.optile.de/software-license-agreement
 */

namespace Optile\Request;

require_once __DIR__.'/../Response/ResponseFactory.php';

/**
 * ReloadListRequest
 *
 * Used for reusing/updating of existing List request.
 * Example usecase: if some payment networks becomes unavailable (Optile error
 * or Customer Credit card gets rejected, ...), List will be reloaded and some
 * payment networks will no longer be rendered.
 * For more detailed instructions, please refer to Optile documentation:
 * https://docs.optile.de/archive/PIN/LIST%20Request.html#Update
 *
 **/
class ReloadListRequest extends SimpleRequest {

    /**
     * Request "self" link from Optile and parse response.
     * @return OptileResponse|null
     */
    public function send($method = self::METHOD_GET) {
        $result = parent::send($method);

        $responseFactory = new \Optile\Response\ResponseFactory();
        $optileResponse = $responseFactory->BuildOptileResponse($result);

        return $optileResponse;
    }
}
