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
        'status',
        'ticket_code',
    ];

    protected $casts = [
        'seat_number' => 'array', // Convierte seat_number a un array
    ];

    // Relación con MovieFunction
    public function movieFunction()
    {
        return $this->belongsTo(MovieFunction::class);
    }

    protected static function booted()
{
    static::deleting(function ($ticket) {
        // Validar que los asientos están configurados correctamente
        if (empty($ticket->seat_number) || !is_array($ticket->seat_number)) {
            throw new \Exception('Los asientos del ticket no están configurados correctamente');
        }

        // Obtener la función asociada
        $movieFunction = $ticket->movieFunction;
        if (!$movieFunction) {
            throw new \Exception('No se encontró la función asociada al ticket');
        }

        // Obtener la sala (Room) asociada a la función
        $room = $movieFunction->room;
        if (!$room) {
            throw new \Exception('No se encontró la sala asociada a la función');
        }

        // Decodificar los asientos
        $seats = json_decode($room->seats, true);
        if (!is_array($seats)) {
            throw new \Exception('Los asientos de la sala no están configurados correctamente');
        }

        // Actualizar los asientos relacionados al ticket
        foreach ($ticket->seat_number as $seat) {
            if (isset($seats[$seat])) {
                $seats[$seat] = false;
            }
        }

        // Guardar los cambios en la sala
        $room->seats = json_encode($seats);
        $room->save();
        });
    }
}

