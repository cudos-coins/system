<?php

namespace CC\Http\Controllers\API;

use CC\APIKey;
use CC\Http\Controllers\Controller;
use CC\Http\Controllers\API\HAL\PaginationTrait;
use CC\Http\Requests\API\Resource\Delete\APIKeyRequest as DeleteRequest;
use CC\Http\Requests\API\Resource\Fetch\DetailsRequest as DetailsRequest;
use CC\Http\Requests\API\Resource\Fetch\PaginatedQueryRequest as FetchRequest;
use CC\Http\Requests\API\Resource\Update\Request as UpdateRequest;
use Illuminate\Support\Facades\Hash;
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
     * @param FetchRequest $request The request incl. auth and validation.
     * @return array
     * @todo Add an admin mode, add a maximum limit to the config.
     */
    public function index(FetchRequest $request)
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
     * @param DetailsRequest $request The request incl. auth and validation.
     * @param APIKey $key
     * @return array
     */
    public function show(DetailsRequest $request, APIKey $key)
    {
        return $key;
    } // function

    /**
     * Store a newly created resource in storage.
     * @param UpdateRequest $request The request incl. auth and validation.
     * @return Response
     */
    public function store(UpdateRequest $request)
    {
        $userId = (int) $request->userId;

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
     * @param DeleteRequest $request Request incl. auth and validation.
     * @param APIKey $key
     * @return Response
     */
    public function destroy(DeleteRequest $request, APIKey $key)
    {
        $key->delete();

        return $key;
    } // function
}
