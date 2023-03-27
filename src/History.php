<?php

namespace Panoscape\History;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    /**
    * Indicates if the model should be timestamped.
    *
    * @var bool
    */
    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
        'performed_at' => 'datetime'
    ];

    /**
    * The attributes that are not mass assignable.
    *
    * @var array
    */
    protected $guarded = [];

    /**
    * The attributes that should be hidden for arrays.
    *
    * @var array
    */
    protected $hidden = [];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable(config('history.histories_table'));
        parent::__construct($attributes);
    }

    /**
     * Get the user who performed this record
     */
    public function user()
    {
        return $this->hasUser()? $this->morphTo()->first(): null;
    }

    /**
     * Returns whether or not a user type/id are present.
     *
     * @return bool
     */
    public function hasUser()
    {
        return !empty($this->user_type) && !empty($this->user_id);
    }

    /**
     * Get the model of this record
     */
    public function model()
    {
        return $this->morphTo()->first();
    }
}