<?php

namespace CC\Http\Controllers\API;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use CC\Http\Requests;
use CC\Http\Controllers\Controller;

/**
 * Handles the user login.
 * @author b3nl <code@b3nl.de>
 * @category controllers
 * @package CC\Http
 * @subpackage API
 * @version $id$
 */
class AccessController extends Controller
{
    /**
     * The default ttl for the access token.
     * @var int.
     */
    const AUTH_DEFAULT_TTL = 3600;

    /**
     * Creates a token for the given users.
     * @param Guard $auth
     * @param Request $request
     * @return array
     * @todo Encrypt the balance of the users.
     */
    protected function createToken(Guard $auth, Request $request)
    {
        $ttl = getenv('AUTH_TTL') ?: self::AUTH_DEFAULT_TTL;

        return [
            "token" => [
                "value" => Crypt::encrypt(serialize([
                    'id' => $auth->user()->id,
                    'ip' => Hash::make($request->getClientIp(), ['rounds' => 4]), // harden a known part of the hash.
                    'lifetime' => [
                        'start' => $start = time(),
                        'end' => $start + $ttl + mt_rand(1, $ttl), // harden a known part of the hash.
                    ],
                    'nonce' => Str::random(32),
                ])),
                "ttl" => $ttl
            ]
        ];
    } // function

    /**
     * Login to the api.
     * @param Guard $auth
     * @param Request $request
     * @return array
     * @todo Check for Remote Addr and correct credentials, count, ip block. Refactor.
     */
    public function store(Guard $auth, Request $request)
    {
        $login = $request->only(['email', 'password']);
        $validator = Validator::make($login, ['email' => 'required|email', 'password' => 'required']);

        if ($validator->fails()) {
            abort(404);
        } // if

        if (!$auth->once($login)) {
            abort(403);
        } // if

        return $this->createToken($auth, $request);
    } // function

    /**
     * Updates the given access token.
     * @param Guard $auth
     * @param Request $request
     * @return array
     */
    public function update(Guard $auth, Request $request)
    {
        return $this->createToken($auth, $request);
    } // function
}
