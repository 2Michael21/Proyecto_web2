<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['movie_function_id', 'seat_number', 'purchased_at'];

    public function movieFunction()
    {
        return $this->belongsTo(MovieFunction::class);
    }
}
