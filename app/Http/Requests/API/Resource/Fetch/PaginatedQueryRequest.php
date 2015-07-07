<?php

namespace CC\Http\Requests\API\Resource\Fetch;

use CC\Http\Requests\API\RequestAbstract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Handles the pagination request of resources.
 * @author b3nl <code@b3nl.de>
 * @category Requests
 * @package CC\Http
 * @subpackage API\Resource\Fetch
 * @version $id$
 */
class PaginatedQueryRequest extends RequestAbstract
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize(Authenticatable $user)
    {
        $userId = $this->get('filter[user_id]', null, true);
        $return = (bool) $user->is_admin;

        if (!$return) {
            if ($userId === null) {
                abort(400);
            } // if

            $return = $userId === $user->id;
        } // if

        return $return;
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
        return [
            'filter' => 'array',
            'filter.user_id' => 'numeric',
            'limit' => 'Integer',
            'skip' => 'Integer',
            'sorting' => 'array'
        ];
    } // function
}
