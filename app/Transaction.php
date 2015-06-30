<?php

namespace CC;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be casted to native types.
     * @var array
     */
    protected $casts = ['finished' => 'boolean'];

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
        1 => 'from_user_id',
        2 => 'to_user_id',
        3 => 'amount',
        4 => 'description',
        5 => 'finished',
        6 => 'planned_date',
        7 => 'processed_date',
        8 => 'signature',
        9 => 'created_at',
        10 => 'updated_at',
        11 => 'last_transaction_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     * @var array
     */
    protected $hidden = ['deleted_at', 'last_transaction_id'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    /**
     * Getter for users.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fromUser()
    {
        return $this->belongsTo('CC\User', 'from_user_id');
    } // function

    /**
     * Getter for users.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function toUser()
    {
        return $this->belongsTo('CC\User', 'to_user_id');
    } // function

}
