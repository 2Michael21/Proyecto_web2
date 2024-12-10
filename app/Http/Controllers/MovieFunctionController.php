<?php

namespace App\Http\Controllers;

use App\Models\MovieFunction;
use Illuminate\Http\Request;

class MovieFunctionController extends Controller
{
    // Listar todas las funciones
    public function index()
    {
        $functions = MovieFunction::with(['movie', 'room'])->get();
        return response()->json($functions);
    }

    // Mostrar una función específica
    public function show($id)
    {
        $function = MovieFunction::with(['movie', 'room'])->find($id);

        if (!$function) {
            return response()->json(['message' => 'Funcion de la pelicula no encontrada'], 404);
        }

        return response()->json($function);
    }

    // Crear una nueva función
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $function = MovieFunction::create($validated);

        return response()->json($function->load(['movie', 'room']), 201);
    }

    // Actualizar una función existente
    public function update(Request $request, $id)
    {
        $function = MovieFunction::find($id);

        if (!$function) {
            return response()->json(['message' => 'Funcion de la pelicula no encontrada'], 404);
        }

        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $function->update($validated);

        return response()->json($function->load(['movie', 'room']));
    }

    // Eliminar una función
    public function destroy($id)
    {
        $function = MovieFunction::find($id);

        if (!$function) {
            return response()->json(['message' => 'Funcion de la pelicula no encontrada'], 404);
        }

        $function->delete();

        return response()->json(['message' => 'Funcion de la pelicula eliminada']);
    }
}
