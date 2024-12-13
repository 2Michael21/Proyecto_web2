<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    // Asegúrate de que `seat_number` sea un array de números de asiento

        protected $fillable = [
            'movie_function_id',
            'seat_number',  // Asegúrate de incluir esta columna
            'status',
            'ticket_code',
        ];
    

    // Convertir seat_number a un array cuando se acceda
    protected $casts = [
        'seat_number' => 'array',  // Esto permite que seat_number sea tratado como un array
    ];

    // Relación con MovieFunction
    public function movieFunction()
    {
        return $this->belongsTo(MovieFunction::class);
    }
}
