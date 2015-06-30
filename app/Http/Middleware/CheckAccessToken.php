<?php

namespace CC\Http\Middleware;

use Closure;
use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Class CheckAccessToken
 * @author blange <code@b3nl.de>
 * @category middleware
 * @package CC\Http
 * @version $id$
 */
class CheckAccessToken
{
    use AppNamespaceDetectorTrait;

    /**
     * The Guard implementation.
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     * @param  Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    } // function

    /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @todo Check for tokens. (401 if it is not there. 403 if it is wrong.) Refactor.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-' . str_replace(['/', '\\'], '', $this->getAppNamespace()) . '-ACCESS-TOKEN');

        if (!$token) {
            abort(401);
        } // if

        try {
            if (!(($tokenData = @Crypt::decrypt($token)) && ($tokenArray = @unserialize($tokenData)))) {
                abort(400);
            } // if

            $validator = Validator::make(
                $tokenArray,
                [
                    'id' => 'required|integer',
                    'ip' => 'required',
                    'lifetime' => 'required|array',
                    'nonce' => 'required'
                ]
            );

            if ($validator->fails()) {
                abort(400);
            } // if

            if (!($tokenArray['ip'] && Hash::check($request->getClientIp(), $tokenArray['ip']))) {
                abort(403);
            } // if

            $time = time();

            if (!(@$tokenArray['lifetime']['start'] && $tokenArray['lifetime']['start'] <= $time &&
                @$tokenArray['lifetime']['end'] && $time < $tokenArray['lifetime']['end'])
            ) {
                abort(403);
            } // TODO Check ttl

            try {
                if (!$this->auth->loginUsingId($tokenArray['id'], false)) {
                    abort(403);
                } // if
            } catch (\Exception $exc) {
                exit($exc);
            } // catch

            return $next($request);
        } catch (DecryptException $exc) {
            abort(403);
        } // catch
    } // function
}
