<?php

namespace CC\Http\Controllers\API;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use CC\APIKey;
use CC\Http\Controllers\Controller;

class APIKeysController extends Controller
{
    /**
     * Returns a list of api keys.
     * @param Authenticatable $user
     * @param Request $request
     * @return array
     * @todo Add an admin mode, add a maximum limit to the config.
     */
    public function index(Authenticatable $user, Request $request)
    {
        $key = new APIKey();

        $filter = $request->get('filter', []);

        if (!@$filter['user_id']) { // TODO Admin mode.
            abort(400);
        } // if

        if ((int)$filter['user_id'] !== $user->id) {
            abort(403);
        } // if

        if (!$sorting = $request->get('sorting', [])) {
            $sorting['id'] = 'desc';
        } // if

        $hidden = $key->getHidden();
        $fillable = $key->getFillable();

        // removed the hidden fields.
        $filter = array_diff_key($filter, array_flip($hidden));

        // removed the unknown fields.
        $filter = array_intersect_key($filter, array_flip($fillable));
        $limit = (int)$request['limit'] && $request['limit'] <= 30 * 10 ? $request['limit'] : 30;
        $skip = (int)$request->get('skip', 0);

        if ($request->has('filter')) {
            $request['filter'] = $filter;
        } // if

        if ($request->has('limit')) {
            $request['limit'] = $limit;
        } // if

        if ($request->has('skip') && $skip) {
            $request['skip'] = $skip;
        } // if

        $query = $key->where($filter)->skip($skip)->take($limit);

        foreach ($sorting as $field => $direction) {
            if (in_array($field, $fillable) && !in_array($field, $hidden)) {
                $query->orderBy($field, $direction === 'desc' ? $direction : 'asc');
            } // if
        } // foreach

        $result = $query->get();
        $count = count($result);
        $total = $key->where($filter)->count();

        if (!$count && !$total) {
            abort(404);
        } // if

        $routeName = Route::currentRouteName();
        $possibleRouteParams = ['filter', 'limit', 'sorting', 'skip'];
        $usedRouteParams = [];

        foreach ($possibleRouteParams as $param) {
            if ($request->has($param)) {
                $usedRouteParams[$param] = $request->get($param);
            } // if
        } // foreach

        ksort($usedRouteParams);

        $links = [
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
            'self' => ['href' => route($routeName, $usedRouteParams)],
        ];

        if (!$count || $count === $total) {
            unset($links['next'], $links['prev']);
        } // if

        return [
            '_embedded' => $result,
            '_links' => $links,
            'count' => $count,
            'total' => $total
        ];
    } // function

    /**
     * Displays the given api_key.
     * @param APIKey $key
     * @return array
     */
    public function show(APIKey $key)
    {
        return $key;
    } // function

    /**
     * Store a newly created resource in storage.
     * @param Request $request The request.
     * @return Response
     * @todo Add Admin mode.
     */
    public function store(Authenticatable $user, Request $request)
    {
        if (!$userId = (int)$request->userId) {
            abort(400);
        } // if

        if ($user->id !== $userId) { // TODO Add admin mode.
            abort(403);
        } // if

        $key = APIKey::firstOrNew([
            'desc' => $request->get('desc') ?: date('YmdHis'),
            'user_id' => $userId
        ]);

        if ($key->id) {
            abort(409);
        } // if

        $key->hash = Hash::make($hashSource = Str::random(32), ['rounds' => 10]);
        $key->save();

        $key->key = $hashSource;

        return $key;
    } // function

    /**
     * Remove the specified resource from storage.
     * @param Authenticatable $user
     * @param APIKey $key
     * @return Response
     * @todo Check user rights!
     */
    public function destroy(Authenticatable $user, APIKey $key)
    {
        if ($key->user_id !== $user->id) {
            abort(403);
        } // if

        $key->delete();

        return $key;
    } // function
}
