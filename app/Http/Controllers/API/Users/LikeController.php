<?php
namespace CC\Http\Controllers\API\Users;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use CC\Http\Requests;
use CC\Http\Controllers\Controller;
use CC\User;
use CC\Like;

/**
 * Handles the transaction requests.
 * @author b3nl <code@b3nl.de>
 * @category controllers
 * @package CC\Http
 * @subpackage API\Transactions
 * @version $id$
 */
class LikeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * @param User The targeted user
     * @return Response
     * @todo A user object can not be injected with the authenticated user. So get the auth user manually.
     */
    public function store(User $target)
    {
        if (!Auth::check()) {
            abort(401);
        } // if

        $user = Auth::user();

        if ($target->id === $user->id) {
            abort(403);
        } // if

        $like = Like::firstOrNew([
            'target_id' => $target->id,
            'target_type' => 'users',
            'user_id' => $user->id,
        ]);

        if ($like->id) {
            abort(409);
        } // if

        if (!$like->save()) {
            abort(500);
        } // if

        return $like;
    } // function

    /**
     * Remove the specified resource from storage.
     * @param User The targeted user.
     * @return Response
     * @todo Add from parameter.
     */
    public function destroy(User $target)
    {
        if (!Auth::check()) {
            abort(401);
        } // if

        $user = Auth::user();
        $like = Like::firstOrNew([
            'target_id' => $target->id,
            'target_type' => 'users',
            'user_id' => $user->id,
        ]);

        if (!$like->id) {
            abort(403);
        } // if

        if (!$like->delete()) {
            aboert(500);
        } // if

        return $like;
    } // function
}
