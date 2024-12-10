<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
     // Listar todas las películas
     public function index()
     {
         $movies = Movie::with('genres')->get(); // Incluye géneros relacionados
         return response()->json($movies);
     }

     // Mostrar una película específica
     public function show($id)
     {
         $movie = Movie::with('genres')->find($id);

         if (!$movie) {
             return response()->json(['message' => 'Pelicula no encontrada'], 404);
         }

         return response()->json($movie);
     }

     // Crear una película
     public function store(Request $request)
     {
         $validated = $request->validate([
             'title' => 'required|string|max:2551|unique:movies',
             'description' => 'nullable|string',
             'release_date' => 'nullable|date',
             'duration' => 'required|integer|min:1',
             'genres' => 'required|array', // Validar array de géneros
             'genres.*' => 'integer|exists:genres,id', // Validar que los géneros existan
         ],
         [
             'title.unique' => 'El título ya está en uso',
             'title.required' => 'Escribe el título',
             'title.max' => 'El título es muy largo',
             'duration.required' => 'Escribe la duración',
             'duration.integer' => 'La duración debe ser un número',
             'duration.min' => 'La duración debe ser mayor a 0',
             'genres.required' => 'Selecciona al menos un género',
         ]);

         $movie = Movie::create($validated);

         if (isset($validated['genres'])) {
             $movie->genres()->attach($validated['genres']);
         }

         return response()->json($movie->load('genres'), 201);
     }

     // Actualizar una película
     public function update(Request $request, $id)
     {
         $movie = Movie::find($id);

         if (!$movie) {
             return response()->json(['message' => 'Pelicula no encontrada'], 404);
         }

         $validated = $request->validate([
             'title' => 'required|string|max:255|unique:movies,title,' . $id,
             'description' => 'nullable|string',
             'release_date' => 'nullable|date',
             'duration' => 'required|integer|min:1',
             'genres' => 'nullable|array', // Validar array de géneros
             'genres.*' => 'integer|exists:genres,id', // Validar que los géneros existan
         ],
         [
             'title.required' => 'Escribe el título',
             'title.max' => 'El título es muy largo',
             'title.unique' => 'El título ya está en uso',
             'duration.required' => 'Escribe la duración',
             'duration.integer' => 'La duración debe ser un número',
             'duration.min' => 'La duración debe ser mayor a 0',
         ]);

         $movie->update($validated);

         if (isset($validated['genres'])) {
             $movie->genres()->sync($validated['genres']); // Actualiza la relación con sync
         }

         return response()->json($movie->load('genres'));
     }

     // Eliminar una película
     public function destroy($id)
     {
         $movie = Movie::find($id);

         if (!$movie) {
             return response()->json(['message' => 'Pelicula no encontrada'], 404);
         }

         $movie->genres()->detach(); // Eliminar relaciones con géneros
         $movie->delete();

         return response()->json(['message' => 'Pelicula eliminada']);
     }
}
