<?php namespace CC;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The like for users and companies.
 * @author bjoern lange <code@b3nl.de>
 * @category models
 * @package UC
 * @version $id$
 */
class Like extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        0 => 'deleted_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        0 => 'id',
        1 => 'user_id',
        2 => 'target_id',
        3 => 'target_type',
        4 => 'created_at',
        5 => 'updated_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     * @var array
     */
    protected $hidden = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'likes';

    /**
     * Getter for users.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('CC\User', 'user_id');
    } // function
}
