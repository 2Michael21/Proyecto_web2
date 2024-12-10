<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = ['title', 'description', 'release_date', 'duration'];

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }
}
