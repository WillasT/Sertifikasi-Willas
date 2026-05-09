<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    // 1. Show all reservations (prioritizing pending ones)
    public function index()
    {
        Reservation::where('status', 'pending')
            ->where('usage_date', '<', now())
            ->update(['status' => 'rejected']);
        // Load the related User, Room, and Equipment so we can display the details
        $reservations = Reservation::with(['user', 'room', 'equipment'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected', 'completed')") // Show pending first
            ->latest('usage_date')
            ->paginate(15);

        return view('admin.reservations.index', compact('reservations'));
    }

    // 2. Approve the reservation
    public function approve(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $requestedStart = Carbon::parse($reservation->usage_date);
        $requestedEnd = (clone $requestedStart)->addHours($reservation->duration_hours);

        $roomConflict = Reservation::where('room_id', $reservation->room_id)
            ->where('status', 'approved')
            ->where(function ($query) use ($requestedStart, $requestedEnd) {
                $query->where('usage_date', '<', $requestedEnd)
                      ->whereRaw('DATE_ADD(usage_date, INTERVAL duration_hours HOUR) > ?', [$requestedStart]);
            })->first();

        if ($roomConflict) {
            return back()->with('error', 'Conflict: Another approved reservation already occupies this room at this time. Please reject this request.');
        }

        $requestedEquipmentIds = $reservation->equipment->pluck('id')->toArray();

        if (!empty($requestedEquipmentIds)) {
            // Check if any of these IDs are already attached to an overlapping APPROVED reservation
            $conflictingEquipmentId = DB::table('equipment_reservation')
                ->whereIn('equipment_id', $requestedEquipmentIds)
                ->whereIn('reservation_id', function ($query) use ($requestedStart, $requestedEnd) {
                    $query->select('id')->from('reservations')
                        ->where('status', 'approved')
                        ->where('usage_date', '<', $requestedEnd)
                        ->whereRaw('DATE_ADD(usage_date, INTERVAL duration_hours HOUR) > ?', [$requestedStart]);
                })->value('equipment_id');

            if ($conflictingEquipmentId) {
                // Find the name of the item to give the admin a helpful error message
                $itemName = \App\Models\Equipment::find($conflictingEquipmentId)->name;

                return back()->with('error', "Equipment Conflict: The requested item ($itemName) was already given to another approved reservation for this time slot. Please reject this request.");
            }
        }

        $reservation->update(['status' => 'approved']);

        return back()->with('status', 'Reservation approved successfully!');
    }

    // 3. Reject the reservation
    public function reject(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $reservation->update(['status' => 'rejected']);

        // Since our conflict checker in the User controller only looks at 'pending' and 'approved',
        // rejecting this request automatically frees up the room and equipment for someone else!

        return back()->with('status', 'Reservation rejected.');
    }

    public function markAsDone(Reservation $reservation)
    {
        if ($reservation->status !== 'approved') {
            return back()->with('error', 'Only approved reservations can be marked as returned/done.');
        }

        $reservation->update([
            'status' => 'done',
            'returned_at' => now(), // Saves the exact current date & time
        ]);

        return back()->with('status', 'Reservation marked as completed and items returned!');
    }
}