<?php

namespace CC;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for the api keys.
 * @author b3nl <code@b3nl.de>
 * @category models
 * @package UC
 * @version $id$
 */
class APIKey extends Model
{
    use SoftDeletes;

    /**
     * Hidden values.
     * @var array
     */
    protected $hidden = ['hash', 'deleted_at'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        0 => 'id',
        1 => 'user_id',
        2 => 'desc',
        3 => 'hash',
        4 => 'created_at',
        5 => 'updated_at',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'api_keys';

    /**
     * Getter for users.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('CC\User', 'user_id');
    } // function
}
