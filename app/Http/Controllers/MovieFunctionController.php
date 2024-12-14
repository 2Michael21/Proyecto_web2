<?php

namespace App\Http\Controllers;

use App\Models\MovieFunction;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MovieFunctionController extends Controller
{
    private $baseURL;
    private $imageBaseURL;
    private $apiKey;

    public function __construct()
    {
        // Inicializar las URLs y la clave de API desde el archivo .env
        $this->baseURL = env('MOVIE_DB_BASE_URL');  // URL base de la API
        $this->imageBaseURL = env('MOVIE_DB_IMAGE_BASE_URL');  // URL base para imágenes
        $this->apiKey = env('MOVIE_DB_API_KEY');  // Clave de API
    }

    // Listar todas las funciones
    public function index()
    {
        $functions = MovieFunction::with(['movie', 'room'])->get();

        // Agregar las URLs completas de las imágenes de las películas
        foreach ($functions as &$function) {
            $movie = $function->movie;
            if ($movie) {
                $movie->poster_full_path = $this->getPosterUrl($movie);
                $movie->backdrop_full_path = $this->getBackdropUrl($movie);
            }
        }

        return response()->json($functions);
    }

    // Mostrar una función específica
    public function show($id)
    {
        $function = MovieFunction::with(['movie', 'room'])->find($id);

        if (!$function) {
            return response()->json(['message' => 'Funcion de la película no encontrada'], 404);
        }

        // Agregar las URLs completas de las imágenes de la película
        $movie = $function->movie;
        if ($movie) {
            $movie->poster_full_path = $this->getPosterUrl($movie);
            $movie->backdrop_full_path = $this->getBackdropUrl($movie);
        }

        return response()->json($function);
    }

    // Función auxiliar para obtener la URL completa del poster
    private function getPosterUrl(Movie $movie)
    {
        return "{$this->imageBaseURL}/w500" . $movie->poster_path;  // Asumiendo que 'poster_path' es el campo en Movie
    }

    // Función auxiliar para obtener la URL completa del backdrop
    private function getBackdropUrl(Movie $movie)
    {
        return "{$this->imageBaseURL}/w780" . $movie->backdrop_path;  // Asumiendo que 'backdrop_path' es el campo en Movie
    }

    // Crear una nueva función
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',  // Usar el ID de la película guardada
            'room_id' => 'required|exists:rooms,id',    // Sala donde se proyectará la película
            'start_time' => 'required|date',             // Hora de inicio de la función
            'end_time' => 'required|date|after:start_time', // Hora de finalización
        ]);

        // Crear la nueva función de la película en la sala
        $function = MovieFunction::create($validated);

        return response()->json($function->load(['movie', 'room']), 201);
    }

    // Actualizar una función existente
    public function update(Request $request, $id)
    {
        $function = MovieFunction::find($id);

        if (!$function) {
            return response()->json(['message' => 'Funcion de la película no encontrada'], 404);
        }

        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        // Validar solapamiento
        $overlap = MovieFunction::where('room_id', $validated['room_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })
            ->exists();

        if ($overlap) {
            return response()->json(['message' => 'El horario de la función se solapa con otra función.'], 400);
        }

        $function->update($validated);

        return response()->json($function->load(['movie', 'room']));
    }

    // Eliminar una función
    public function destroy($id)
    {
        $function = MovieFunction::find($id);

        if (!$function) {
            return response()->json(['message' => 'Funcion de la película no encontrada'], 404);
        }

        $function->delete();

        return response()->json(['message' => 'Funcion de la película eliminada']);
    }
}
