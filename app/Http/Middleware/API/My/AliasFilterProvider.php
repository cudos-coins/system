<?php

namespace CC\Http\Middleware\API\My;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Adds the filter value for the user id to the actual request virtually.
 * @author blange <code@b3nl.de>
 * @category Middleware
 * @package CC\Http
 * @subpackage API\My
 * @version $id$
 */
class AliasFilterProvider
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($user = Auth::user()) { // TODO Check Request style.
            $request->merge(['filter' => array_merge(['user_id' => $user->id], $request->get('filter', []))]);
        } // if

        return $next($request);
    } // function
}
