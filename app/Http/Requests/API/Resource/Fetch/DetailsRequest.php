<?php
namespace CC\Http\Requests\API\Resource\Fetch;

use CC\APIKey;
use CC\Http\Requests\API\RequestAbstract;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Handles the details request of a resource.
 * @author b3nl <code@b3nl.de>
 * @category Requests
 * @package CC\Http
 * @subpackage API\Resource\Fetch
 * @version $id$
 */
class DetailsRequest extends RequestAbstract
{
    /**
     * Determine if the user is authorized to make this request.
     * @param Authenticatable $user The authed user.
     * @return bool
     */
    public function authorize(Authenticatable $user)
    {
        return $user->is_admin || APIKey::whereIdAndUserId($this->route('api_keys'), $user->id)->exists();
    } // function
}
