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

    // Evento Eloquent para ejecutar acciones al eliminar un ticket
    protected static function booted()
    {
        static::deleting(function ($ticket) {
            // Verificar que el ticket tenga asientos asignados
            if (!is_array($ticket->seat_number) || empty($ticket->seat_number)) {
                throw new \Exception('Los asientos del ticket no están configurados correctamente');
            }

            // Obtener la función asociada
            $movieFunction = $ticket->movieFunction;
            if (!$movieFunction) {
                throw new \Exception('No se encontró la función asociada al ticket');
            }

            // Obtener la sala (Room) asociada a la función
            $room = $movieFunction->room; // Asegúrate de que la relación está definida
            if (!$room) {
                throw new \Exception('No se encontró la sala asociada a la función');
            }

            // Decodificar los asientos
            $seats = json_decode($room->seats, true);

            // Marcar los asientos del ticket como disponibles (false)
            foreach ($ticket->seat_number as $seat) {
                if (isset($seats[$seat])) {
                    $seats[$seat] = false;
                }
            }

            // Guardar los cambios en los asientos de la sala
            $room->seats = json_encode($seats);
            $room->save();
        });
    }
}
