<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Http\Requests\BookingRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Movie;

class BookingController extends Controller
{
    public function index() {
        $user = request()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'You must be logged in to view bookings'
            ], 401);
        }

        // Regular users can only see their own bookings
        // Admins can see all bookings
        if ($user->is_admin ?? false) {
            $bookings = Booking::with('user','movie')->get();
        } else {
            $bookings = Booking::where('user_id', $user->id)
                ->with('user','movie')
                ->get();
        }
        
        return response()->json($bookings);
    }

    public function userBookings($user_id){
        $user = request()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'You must be logged in to view bookings'
            ], 401);
        }

        // Ensure user can only view their own bookings
        if ($user->id != $user_id) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'You can only view your own bookings'
            ], 403);
        }

        $bookings = Booking::where('user_id', '=', $user_id)
            ->with('user', 'movie')
            ->get();
        return response()->json($bookings);
    }

    // show and edit
    function show(string $id) {
        $user = request()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'You must be logged in to view bookings'
            ], 401);
        }

        $booking = Booking::with('user', 'movie')->find($id);
        
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        // Users can only view their own bookings, admins can view all
        if (!$user->is_admin && $booking->user_id != $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'You can only view your own bookings'
            ], 403);
        }

        return response()->json($booking);
    }

    function destroy($id){
        $user = request()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'You must be logged in to delete bookings'
            ], 401);
        }

        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        // Users can only delete their own bookings, admins can delete any
        if (!$user->is_admin && $booking->user_id != $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'You can only delete your own bookings'
            ], 403);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully'
        ], 200);
    }


    function store(BookingRequest $request){
        // Get authenticated user
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'You must be logged in to create a booking'
            ], 401);
        }

        // Ensure user_id matches authenticated user (authorization check)
        if ($request->user_id != $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'You can only create bookings for yourself'
            ], 403);
        }

        // Check if seat is already booked for this movie and date
        $existingBooking = Booking::where('movie_id', $request->movie_id)
            ->where('party_date', $request->party_date)
            ->where('seat_number', $request->seat_number)
            ->first();
            
        if ($existingBooking) {
            return response()->json([
                'message' => 'Seat is already booked',
                'error' => 'This seat is already reserved for the selected show'
            ], 409);
        }
        
        // Price is already calculated on frontend (seat price + extras portion)
        // Just store it as received
        $extras = $request->extras ?? [];
        
        $booking = new Booking();
        $booking->movie_id = $request->movie_id;
        $booking->user_id = $user->id; // Use authenticated user ID
        $booking->seat_number = $request->seat_number;
        $booking->party_date = $request->party_date;
        $booking->party_number = $request->party_number;
        $booking->price = $request->price; // Price already includes seat + extras portion
        $booking->extras = $extras; // Store extras as JSON
        $booking->save();
        
        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking->load('user', 'movie')
        ], 201);
    }

    function update(BookingRequest $request, string $id){
        $user = request()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'You must be logged in to update bookings'
            ], 401);
        }

        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        // Users can only update their own bookings, admins can update any
        if (!$user->is_admin && $booking->user_id != $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'You can only update your own bookings'
            ], 403);
        }

        // Ensure user_id matches authenticated user
        if ($request->user_id != $user->id && !$user->is_admin) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'You can only update bookings for yourself'
            ], 403);
        }

        $booking->user_id = $request->user_id;
        $booking->movie_id = $request->movie_id;
        $booking->seat_number = $request->seat_number;
        $booking->party_date = $request->party_date;
        $booking->party_number = $request->party_number;
        $booking->price = $request->price;
        $booking->extras = $request->extras ?? $booking->extras;
        $booking->save();
        
        return response()->json([
            'message' => 'Booking updated successfully',
            'booking' => $booking->load('user', 'movie')
        ], 200);
    }

    function booked_seats($party_date, $movie_id){
        $booked_seats = DB::table('bookings')
            ->where('party_date', '=', $party_date)
            ->where('movie_id', '=', $movie_id)
            ->pluck('seat_number')
            ->toArray();
        return response()->json($booked_seats);
    }

}
