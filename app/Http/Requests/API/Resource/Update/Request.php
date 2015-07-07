<?php

namespace CC\Http\Requests\API\Resource\Update;

use CC\Http\Requests\API\RequestAbstract;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Handles the update request of a resource.
 * @author b3nl <code@b3nl.de>
 * @category Requests
 * @package CC\Http
 * @subpackage API\Resource\Update
 * @version $id$
 */
class Request extends RequestAbstract
{
    /**
     * Determine if the user is authorized to make this request.
     * @param Authenticatable $user The authed user.
     * @return bool
     */
    public function authorize(Authenticatable $user)
    {
        return $user->is_admin || $user->id === (int) $this->get('userId');
    } // function

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        return [
            'userId' => 'required|integer',
        ];
    }
}
