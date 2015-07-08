<?php

namespace CC\Http\Requests\API\Resource\Delete;

use CC\Http\Requests\API\RequestAbstract;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Removes a like of an resource.
 * @author b3nl <code@b3nl.de>
 * @category Requests
 * @package CC\Http
 * @subpackage API\Resource\Insert
 * @version $id$
 */
class RemoveLikeRequest extends RequestAbstract
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize(Authenticatable $user)
    {
        $from = (int) ($this->get('from', 0) ?: $user->id);

        return $user->is_admin || $user->id === $from;
    } // function

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        return [
            'from' => 'integer'
        ];
    }
}
