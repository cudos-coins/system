<?php

namespace CC\Http\Requests\API\Resource\Insert;

use CC\Http\Requests\API\RequestAbstract;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Likes a resource.
 * @author b3nl <code@b3nl.de>
 * @category Requests
 * @package CC\Http
 * @subpackage API\Resource\Insert
 * @version $id$
 */
class LikeRequest extends RequestAbstract
{
    /**
     * Determine if the user is authorized to make this request.
     * @param Authenticatable $user The logged in user.
     * @return bool
     */
    public function authorize(Authenticatable $user)
    {
        $from = (int) ($this->get('from', 0) ?: $user->id);
        $target = $this->route('users');

        return ($user->is_admin || $user->id === $from) && ($target->id !== $from);
    } // function

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'from' => 'integer'
        ];
    }
}
