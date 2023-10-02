<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        // Get the bookings of the authenticated user
        $bookings = auth()->user()->bookings()->with('service')->paginate(10);

        return view('user.booking', compact('bookings'));
    }

    public function adminView()
    {
        $bookings = Booking::with(['user', 'service'])->paginate(10); // Fetching 10 bookings per page.
        return view('admin.booking', compact('bookings'));
    }

    public function showAllBookings(Request $request)
    {
        $status = $request->input('status');
        $bookings = Booking::with('user', 'service');

        if ($status) {
            $bookings = $bookings->where('status', $status);
        }

        $bookings = $bookings->paginate(10);


        return view('admin.booking', compact('bookings'));
    }



    public function cancel($id)
    {
        $booking = Booking::find($id);

        // Check if the booking exists and belongs to the authenticated user
        if ($booking && $booking->user_id == auth()->id()) {
            // Only allow cancellation if it's not completed
            if ($booking->status !== 'completed') {
                $booking->status = 'cancelled';
                $booking->save();

                return redirect()->route('user.booking')->with('success', 'Booking cancelled successfully.');
            } else {
                return redirect()->route('user.booking')->with('error', 'Booking cannot be cancelled as it is already completed.');
            }
        } else {
            return redirect()->route('user.booking')->with('error', 'Booking not found.');
        }
    }

    public function assignStaff(Request $request, Booking $booking)
    {
        $booking->staff_id = $request->staff_id;
        $booking->save();

        return redirect()->back()->with('success', 'Staff member assigned successfully.');
    }
}