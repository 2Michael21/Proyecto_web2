<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\MovieFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    // Listar todos los boletos
    public function index()
    {
        $tickets = Ticket::with(['movieFunction.movie', 'movieFunction.room'])->get(); // Incluye la función y la sala
        return response()->json($tickets);
    }

    // Mostrar un boleto específico
    public function show($id)
    {
        $ticket = Ticket::with('movieFunction')->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        // Cargar la película y la sala
        $ticket = $ticket->load(['movieFunction.movie', 'movieFunction.room']);

        return response()->json($ticket);
    }

    // Crear uno o varios boletos (cuando el cliente compra uno o más asientos)
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'movie_function_id' => 'required|exists:movie_functions,id',
            'seat_numbers' => 'required|array', // Una lista de números de asientos
            'seat_numbers.*' => 'required|string|max:10', // Validar cada asiento individualmente
        ]);

        // Generar un único código de ticket para todos los asientos
        $ticketCode = Str::random(8);

        // Obtener la función de la película y la sala
        $movieFunction = MovieFunction::with('room')->find($validated['movie_function_id']);
        if (!$movieFunction) {
            return response()->json(['message' => 'Función de película no encontrada'], 404);
        }

        // Verificar si algún asiento ya está ocupado
        foreach ($validated['seat_numbers'] as $seatNumber) {
            $existingTicket = Ticket::where('movie_function_id', $validated['movie_function_id'])
                ->where('seat_number', $seatNumber)
                ->first();

            if ($existingTicket && $existingTicket->status == 'ocupado') {
                return response()->json(['message' => 'El asiento ' . $seatNumber . ' ya está ocupado'], 422);
            }
        }

        // Iniciar una transacción para asegurar la consistencia de la base de datos
        DB::beginTransaction();

        try {
            // Crear un único ticket para todos los asientos
            $ticket = Ticket::create([
                'movie_function_id' => $validated['movie_function_id'],
                'seat_number' => implode(', ', $validated['seat_numbers']),  // Listar todos los asientos comprados en un solo campo
                'status' => 'ocupado',  // Asegurarnos de que el estado sea "ocupado"
                'ticket_code' => $ticketCode, // Usar el mismo código para todos los boletos
            ]);

            // Confirmar la transacción
            DB::commit();

            // Obtener la sala y la película asociada
            $room = $movieFunction->room;
            $movie = $movieFunction->movie;

            // Devolver los detalles del ticket creado con la sala y la película
            return response()->json([
                'ticket_code' => $ticketCode,  // Devolver el código generado
                'movie_title' => $movie->title, // Título de la película
                'room_name' => $room->name,     // Nombre de la sala
                'seat_numbers' => $validated['seat_numbers'], // Asientos comprados
                'message' => 'Ticket creado correctamente para los asientos: ' . implode(', ', $validated['seat_numbers']),
            ], 201);

        } catch (\Exception $e) {
            // En caso de error, revertir la transacción
            DB::rollBack();
            return response()->json(['message' => 'Hubo un error al crear el ticket'], 500);
        }
    }

    // Buscar un boleto por código
    public function showByCode($ticketCode)
    {
        $ticket = Ticket::where('ticket_code', $ticketCode)->first();

        if (!$ticket) {
            return response()->json(['message' => 'Ticket con el código ' . $ticketCode . ' no encontrado'], 404);
        }

        $movieFunction = $ticket->movieFunction;  // Obtener la función de la película
        $movie = $movieFunction->movie;  // Obtener la película
        $room = $movieFunction->room;   // Obtener la sala

        // Obtener los números de los asientos
        $seatNumbers = explode(', ', $ticket->seat_number);

        return response()->json([
            'ticket_code' => $ticketCode,
            'movie_title' => $movie->title,
            'room_name' => $room->name,
            'seat_numbers' => $seatNumbers,
        ]);
    }

    // Actualizar un boleto
    public function update(Request $request, $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        $validated = $request->validate([
            'movie_function_id' => 'required|exists:movie_functions,id',
            'seat_number' => 'required|string|max:10',
        ]);

        // Verificar si el asiento ya está ocupado
        $existingTicket = Ticket::where('movie_function_id', $validated['movie_function_id'])
            ->where('seat_number', $validated['seat_number'])
            ->where('id', '!=', $id) // Excluir el ticket actual
            ->first();

        if ($existingTicket && $existingTicket->status == 'ocupado') {
            return response()->json(['message' => 'Puesto ya tomado'], 422);
        }

        $ticket->update($validated);

        return response()->json($ticket->load(['movieFunction.movie', 'movieFunction.room']));
    }

    // Eliminar un boleto
    public function destroy($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        $ticket->delete();

        return response()->json(['message' => 'Ticket eliminado']);
    }

    // Liberar los asientos de una función de película (cuando la película termine)
    public function liberarAsientos($movieFunctionId)
    {
        // Actualizar los tickets de una función específica para marcar los asientos como "libres"
        $tickets = Ticket::where('movie_function_id', $movieFunctionId)->get();

        foreach ($tickets as $ticket) {
            $ticket->update(['status' => 'libre']);  // Cambiar el estado del ticket a "libre"
        }

        return response()->json(['message' => 'Los asientos han sido liberados'], 200);
    }
}
