<?php

namespace CC\Http\Controllers\API\My;

use CC\Http\Controllers\API\APIKeysControllerTest as BaseTestCase;
use Illuminate\Support\Facades\DB;

require_once realpath(__DIR__ . '/../APIKeysControllerTest.php');

/**
 * Testing of the controller.
 * @author blange <code@b3nl.de>
 * @category Controllers
 * @package CC\Http
 * @subpackage API
 * @version $id$
 */
class APIKeysControllerTest extends BaseTestCase
{
    /**
     * Returns the typical resource url.
     * @return string
     */
    protected function getResourceURL()
    {
        return '/api/my/api_keys/';
    } // function

    /**
     * Returns the route name.
     * @return string
     */
    protected function getRouteName()
    {
        return 'api.my.api_keys.index';
    } // function

    /**
     * Returns a valid user filter.
     * @param bool $correctData Should the correct data be returned?
     * @return array
     */
    protected function getUserFilter($correctData = true)
    {
        $return = [];

        if (!$correctData) {
            $return = ['filter' => ['user_id' => uniqid()]];
        } // if

        return $return;
    } // function

    /**
     * Checks if the correct status code is returned.
     * @return void
     */
    public function testIndexErrorNoUserFilter()
    {
        // not used for the alias call.
    } // function
}
