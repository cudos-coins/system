<?php

namespace CC\Http\Controllers\API\HAL;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Helps the request with the HAL Pagination.
 * @author b3nl <code@b3nl.de>
 * @category controllers
 * @package CC\Http
 * @subpackage API\HAL
 * @version $id$
 */
trait PaginationTrait
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
        if (!$routeName) {
            $routeName = Route::currentRouteName();
        } // if

        $count = count($response['data']);
        $limit = $sanitizedRequestData['limit'];
        $possibleRouteParams = ['filter', 'limit', 'sorting', 'skip'];
        $response = array_merge($response, ['_embedded' => $response['data'], '_links' => []]);
        $skip = $sanitizedRequestData['skip'];
        $total = @$response['total'];
        $usedRouteParams = [];

        foreach ($possibleRouteParams as $param) {
            if ($request->has($param)) {
                $usedRouteParams[$param] = $request->get($param);
            } // if
        } // foreach

        ksort($usedRouteParams);
    
        $response['_links'] = [
            'first' => ['href' => route($routeName, array_merge($usedRouteParams, ['skip' => 0]))],
            'last' => [
                'href' => route(
                    $routeName,
                    array_merge($usedRouteParams, [
                        'skip' => $limit > $total
                            ? 0
                            : ($total % $limit
                                ? $total - ($total % $limit)
                                : $total - $limit)
                    ])
                )
            ],
            'next' => [
                'href' => route(
                    $routeName,
                    array_merge($usedRouteParams, ['skip' => $skip + $limit >= $total ? $skip : $skip + $limit])
                )
            ],
            'prev' => [
                'href' => route(
                    $routeName,
                    array_merge($usedRouteParams, ['skip' => $skip - $limit < 0 ? 0 : $skip - $limit])
                )
            ],
            'self' => ['href' => rtrim(route($routeName, $usedRouteParams), '?')],
        ];

        if (!$count || $count === $total) {
            unset($response['_links']['next'], $response['_links']['prev']);
        } // if

        unset($response['data']);

        return $response;
    } // function
}
