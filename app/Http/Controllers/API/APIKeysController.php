<?php

namespace CC\Http\Controllers\API;

use CC\APIKey;
use CC\Http\Controllers\Controller;
use CC\Http\Controllers\API\HAL\PaginationTrait;
use CC\Http\Requests\API\PaginatedQueryRequest;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Handles the api keys.
 * @author b3nl <code@b3nl.de>
 * @category controllers
 * @package CC\Http
 * @subpackage API
 * @version $id$
 */
class APIKeysController extends Controller
{
    use PaginationTrait;

    /**
     * Returns a list of api keys.
     * @param PaginatedQueryRequest $request
     * @return array
     * @todo Add an admin mode, add a maximum limit to the config.
     */
    public function index(PaginatedQueryRequest $request)
    {
        $key = new APIKey();

        $requestData = $request->getSanitizedRequestData($key);

        $query = $key
            ->where($filter = $requestData['filter'])
            ->skip($skip = $requestData['skip'])
            ->take($limit = $requestData['limit']);

        foreach ($requestData['sorting'] as $field => $direction) {
            $query->orderBy($field, $direction);
        } // foreach

        $result = $query->get();
        $count = count($result);
        $total = $key->where($filter)->count();

        if (!$count && !$total) {
            abort(404);
        } // if

        return $this->addHALToResponse(
            $request,
            [
                'data' => $result,
                'count' => $count,
                'total' => $total
            ],
            $requestData
        );
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

        Event::fire('test');

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
