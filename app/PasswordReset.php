<?php

namespace CC;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        0 => 'email',
        1 => 'token',
        2 => 'created_at',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'password_resets';


}
