<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_function_id',
        'seat_number',
        'ticket_code',  // Este campo debe existir en la base de datos
        'status', // Si el status es necesario
    ];

    // Relación con MovieFunction
    public function movieFunction()
    {
        return $this->belongsTo(MovieFunction::class);
    }
}
