<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\Movie;
use App\Http\Requests\MovieRequest;
use Illuminate\Support\Facades\Storage;

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
            // Authorization check - only admins can create
            if (!auth()->check() || !auth()->user()->is_admin) {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

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
        try {
            $movie = Movie::find($id);
            if (!$movie) {
                return response()->json([
                    'message' => 'Movie not found'
                ], 404, [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }
            
            // Ensure all fields are included in response
            $movieData = $movie->toArray();
            
            return response()->json($movieData, 200, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch movie',
                'message' => $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(MovieRequest $request, string $id)
    {
        try {
            // Authorization check - only admins can update
            if (!auth()->check() || !auth()->user()->is_admin) {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $movie = Movie::find($id);

            if (!$movie) {
                return response()->json(['message' => 'Movie not found'], 404);
            }

            // Debug: Log what we received
            \Log::info('Update movie request received', [
                'id' => $id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'duration' => $request->input('duration'),
                'TypeOfFilm' => $request->input('TypeOfFilm'),
                'has_poster_file' => $request->hasFile('poster'),
                'all_input' => $request->all(),
            ]);

            // If there's a new poster file, store it and update the path; otherwise keep existing
            if ($request->hasFile('poster')) {
                // Delete old poster if exists
                if ($movie->poster && Storage::disk('public')->exists($movie->poster)) {
                    Storage::disk('public')->delete($movie->poster);
                }
                $posterPath = $request->file('poster')->store('posters', 'public');
                $movie->poster = $posterPath;
            }

            // Update movie fields - use input() to get values even if they're in FormData
            $movie->title = $request->input('title');
            $movie->description = $request->input('description');
            $movie->TypeOfFilm = $request->input('TypeOfFilm');
            $movie->duration = (int) $request->input('duration');

            $movie->save();

            // Response
            return response()->json([
                'message' => 'Movie updated successfully',
                'movie' => $movie
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error updating movie', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to update movie',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Authorization check - only admins can delete
            if (!auth()->check() || !auth()->user()->is_admin) {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $movie = Movie::find($id);

            if (!$movie) {
                return response()->json(['message' => 'Movie not found'], 404);
            }

            // Delete poster file if exists
            if ($movie->poster && \Storage::disk('public')->exists($movie->poster)) {
                \Storage::disk('public')->delete($movie->poster);
            }

            $movie->delete();

            return response()->json([
                'message' => 'Movie deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete movie',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
