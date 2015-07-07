<?php

namespace CC\Http\Controllers\API;

use CC\Http\Requests\API\LoginRequest as InsertRequest;
use CC\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
     * @param InsertRequest $request
     * @return array
     */
    public function store(Guard $auth, InsertRequest $request)
    {
        if (!$auth->once($request->only(['email', 'password']))) {
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
