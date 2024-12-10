<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
       // Listar todos los boletos
       public function index()
       {
           $tickets = Ticket::with(['movieFunction.movie', 'movieFunction.room'])->get(); // Incluye la función relacionada
           return response()->json($tickets);
       }

       // Mostrar un boleto específico
       public function show($id)
       {
           $ticket = Ticket::with('movieFunction')->find($id);

           if (!$ticket) {
               return response()->json(['message' => 'Ticket no encontrado'], 404);
           }

           return response()->json($ticket->load(['movieFunction.movie', 'movieFunction.room']));
       }

       // Crear un nuevo boleto
       public function store(Request $request)
       {
           $validated = $request->validate([
               'movie_function_id' => 'required|exists:movie_functions,id',
               'seat_number' => 'required|string|max:10',
           ]);

           // Verificar si el asiento ya está ocupado
           $existingTicket = Ticket::where('movie_function_id', $validated['movie_function_id'])
               ->where('seat_number', $validated['seat_number'])
               ->first();

           if ($existingTicket) {
               return response()->json(['message' => 'Puesto ya tomado'], 422);
           }

           $ticket = Ticket::create($validated);

           return response()->json($ticket->load('movieFunction'), 201);
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

           if ($existingTicket) {
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
}
