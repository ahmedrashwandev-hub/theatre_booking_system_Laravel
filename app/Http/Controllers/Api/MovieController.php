<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\Movie;
use App\Http\Requests\MovieRequest;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $movies = Movie::orderBy('id', 'DESC')->get();
            return response()->json($movies, 200, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch movies',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MovieRequest $request)
    {
        try {
            $posterPath = null;

            if ($request->hasFile('poster')) {
                $posterPath = $request->file('poster')->store('posters', 'public');
            }

            // Create movie
            $movie = Movie::create([
                'title' => $request->title,
                'description' => $request->description,
                'poster' => $posterPath,
                'TypeOfFilm' => $request->TypeOfFilm,
                'duration' => $request->duration,
            ]);

            // Response
            return response()->json([
                'message' => 'Movie created successfully',
                'movie' => $movie
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create movie',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $movie = Movie::find($id);
        if (!$movie) {
            return response()->json(['message' => 'Movie not found'], 404);
        }
        return response()->json($movie);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(MovieRequest $request, string $id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json(['message' => 'Movie not found'], 404);
        }

        // If there's a new poster file, store it and update the path; otherwise keep existing
        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')->store('posters', 'public');
            $movie->poster = $posterPath;
        }

        $movie->title = $request->title;
        $movie->description = $request->description;
        $movie->TypeOfFilm = $request->TypeOfFilm;
        $movie->duration = $request->duration;

        $movie->save();

        // Response
        return response()->json([
            'message' => 'Movie created successfully',
            'movie' => $movie
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Movie::find($id)->delete();
        return 'deleted successfully';
    }
}
