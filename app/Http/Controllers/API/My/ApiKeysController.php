<?php

namespace CC\Http\Controllers\API\My;

use CC\Http\Controllers\API\APIKeysController as BaseController;
use Illuminate\Http\Request;

/**
 * Alias for the apikeys controller to prevent setting the user_id manually.
 * @author blange <code@b3nl.de>
 * @category controllers
 * @package CC\Http
 * @subpackage API\My
 * @version $id$
 */
class ApiKeysController extends BaseController
{
    /**
     * Adds the HAL-Data to the request.
     * @param Request $request
     * @param array $response
     * @param array $sanitizedRequestData
     * @param string $routeName
     * @return array
     */
    protected function addHALToResponse(Request $request, array $response, array $sanitizedRequestData, $routeName = '')
    {
        $filter = $request->get('filter', []);

        unset($sanitizedRequestData['filter']['user_id'], $filter['user_id']);
        $request->merge(['filter' => $filter]);

        return parent::addHALToResponse($request, $response, $sanitizedRequestData, $routeName);
    } // function
}
