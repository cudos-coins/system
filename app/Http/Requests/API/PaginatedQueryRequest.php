<?php

namespace CC\Http\Requests\API;

use CC\Http\Requests\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Handles the paginated query request.
 * @author b3nl <code@b3nl.de>
 * @category Requests
 * @package CC\Http
 * @subpackage API
 * @version $id$
 */
class PaginatedQueryRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize(Authenticatable $user)
    {
        $userId = $this->get('filter[user_id]', null, true);

        if ($userId === null) {
            abort(400);
        } // if

        return (int)$userId === $user->id;
    } // function

    /**
     * Returns an data array usable for querieng the table of the model.
     * @param Model $model The used model.
     * @return array
     */
    public function getSanitizedRequestData(Model $model)
    {
        $hidden = $model->getHidden();
        $fillable = $model->getFillable();
        $filter = $this->get('filter', []);
        $return = ['filter' => [], 'sorting' => ['id' => 'desc']];

        // removed the hidden fields.
        $return['filter'] = array_diff_key($filter, array_flip($hidden));
    
        // removed the unknown fields.
        $return['filter'] = array_intersect_key($filter, array_flip($fillable));

        $return['limit'] = (int) $this['limit'] && ($this['limit'] <= 30 * 10) ? $this['limit'] : 30;
        $return['skip'] = $skip = (int) $this->get('skip');
    
        if ($this->has('filter')) {
            $this['filter'] = $return['filter'];
        } // if
    
        if ($this->has('limit')) {
            $this['limit'] = $return['limit'];
        } // if
    
        if ($this->has('skip')) {
            $this['skip'] = $skip;
        } // if

        if ($this->has('sorting')) {
            $return['sorting'] = [];

            foreach ($this->get('sorting', []) as $field => $direction) {
                if (in_array($field, $fillable) && !in_array($field, $hidden)) {
                    $return['sorting'][$field] = $direction === 'desc' ? 'desc' : 'asc';
                } // if
            } // foreach

            $this['sorting'] = $return['sorting'];
        } // if

        return $return;
    } // function

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        return ['filter' => 'required|array', 'filter.user_id' => 'required|numeric', 'limit' => 'Integer', 'skip' => 'Integer', 'sorting' => 'array'];
    } // function
}
