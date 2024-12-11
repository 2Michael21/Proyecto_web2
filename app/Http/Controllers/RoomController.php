<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
      // Listar todas las salas
      public function index()
      {
          $rooms = Room::all();
          return response()->json($rooms);
      }

      // Mostrar una sala específica
      public function show($id)
      {
          $room = Room::find($id);

          if (!$room) {
              return response()->json(['message' => 'Sala no encontrada'], 404);
          }

          return response()->json($room);
      }

      // Crear una nueva sala
      public function store(Request $request)
      {
          $validated = $request->validate([
              'name' => 'required|string|max:50|unique:rooms',
              'capacity' => 'required|integer|min:1|max:30',
          ],
          [
              'name.unique' => 'El nombre ya está en uso',
              'name.required' => 'Escribe el nombre',
              'name.max' => 'El nombre es muy largo',
              'capacity.required' => 'Escribe la capacidad',
              'capacity.integer' => 'La capacidad debe ser un número',
              'capacity.min' => 'La capacidad debe ser mayor a 0',
              'capacity.max' => 'La capacidad no debe ser mayor a 30',
          ]);

          $room = Room::create($validated);

          return response()->json($room, 201);
      }

      // Actualizar una sala
      public function update(Request $request, $id)
      {
          $room = Room::find($id);

          if (!$room) {
              return response()->json(['message' => 'Sala no encontrada'], 404);
          }

          $validated = $request->validate([
              'name' => 'required|string|max:50|unique:rooms,name,' . $id,
              'capacity' => 'required|integer|min:1|max:30',
          ],
          [
              'name.unique' => 'El nombre ya está en uso',
              'name.required' => 'Escribe el nombre',
              'name.max' => 'El nombre es muy largo',
              'capacity.required' => 'Escribe la capacidad',
              'capacity.integer' => 'La capacidad debe ser un número',
              'capacity.min' => 'La capacidad debe ser mayor a 0',
              'capacity.max' => 'La capacidad no debe ser mayor a 30',
          ]);

          $room->update($validated);

          return response()->json($room);
      }

      // Eliminar una sala
      public function destroy($id)
      {
          $room = Room::find($id);

          if (!$room) {
              return response()->json(['message' => 'Sala no encontrada'], 404);
          }

          $room->delete();

          return response()->json(['message' => 'Sala eliminada']);
      }
}
