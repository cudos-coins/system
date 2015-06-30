<?php

namespace CC\Http\Controllers\API;

use CC\Http\Controllers\Controller;

/**
 * Handles the simple info request.
 * @author b3nl <code@b3nl.de>
 * @category controllers
 * @package CC\Http
 * @subpackage API
 * @version $id$
 */
class WelcomeController extends Controller
{
    /**
     * Show the application welcome screen to the user.
     * @return array
     */
    public function index()
    {
        return [
            "author" => "Bjoern Lange",
            "name" => "Universal Coins",
            "src" => "https://github.com/b3nl/universal-coins",
            "time" => date('d.m.Y H:i:s'),
            "timezone" => date('e'),
            "dst" => date('I') ? 'true' : 'false'
        ];
    } // function
}
