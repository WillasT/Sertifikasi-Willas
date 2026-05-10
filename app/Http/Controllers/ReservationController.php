<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::where('user_id', Auth::id())->latest()->get();
        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        $rooms = Room::where('availability_status', 'available')->get();

        // 1. Fetch available equipment and group it by Category, then by Name
        $rawEquipments = Equipment::where('status', 'available')->get();
        $groupedEquipment = [];

        foreach ($rawEquipments as $eq) {
            // Create category array if it doesn't exist
            if (!isset($groupedEquipment[$eq->category])) {
                $groupedEquipment[$eq->category] = [];
            }
            // Add to the count of that specific item name
            if (!isset($groupedEquipment[$eq->category][$eq->name])) {
                $groupedEquipment[$eq->category][$eq->name] = 0;
            }
            $groupedEquipment[$eq->category][$eq->name]++;
        }

        return view('reservations.create', compact('rooms', 'groupedEquipment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'purpose' => 'required|string|max:255',
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'equipment_requests' => 'nullable|array',
        ]);

        $requestedStart = Carbon::parse($validated['reservation_date'] . ' ' . $validated['start_time']);
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);
        $durationHours = max(1, $startTime->diffInHours($endTime));
        $requestedEnd = (clone $requestedStart)->addHours($durationHours);

        // 1. Check for Room Overlaps
        $conflict = Reservation::where('room_id', $validated['room_id'])
            ->where('status', 'approved')
            ->where(function ($query) use ($requestedStart, $requestedEnd) {
                $query->where('usage_date', '<', $requestedEnd)
                    ->whereRaw('DATE_ADD(usage_date, INTERVAL duration_hours HOUR) > ?', [$requestedStart]);
            })->first();

        if ($conflict) {
            return redirect()->back()->withInput()->with('error', 'This room is already booked.');
        }

        $idsToAttach = [];
        $totalRequestedCount = 0;

        // 2. Equipment Availability Logic
        if (!empty($validated['equipment_requests'])) {

            // Find busy equipment during this time slot
            $busyEquipmentIds = DB::table('equipment_reservation')
                ->whereIn('reservation_id', function ($query) use ($requestedStart, $requestedEnd) {
                    $query->select('id')->from('reservations')
                        ->where('status', 'approved')
                        ->where('usage_date', '<', $requestedEnd)
                        ->whereRaw('DATE_ADD(usage_date, INTERVAL duration_hours HOUR) > ?', [$requestedStart]);
                })->pluck('equipment_id')->toArray();

            // Inside the foreach loop of your store method
            foreach ($validated['equipment_requests'] as $itemName => $quantity) {
                if ($quantity > 0) {
                    $totalRequestedCount += $quantity;

                    // 1. Get TOTAL physical items with this name that are 'available' (not in maintenance)
                    $totalPhysicalItems = \App\Models\Equipment::where('name', $itemName)
                        ->where('status', 'available')
                        ->pluck('id')
                        ->toArray();

                    // 2. Find which of THESE specific IDs are busy during this time
                    $busyIdsForThisItem = DB::table('equipment_reservation')
                        ->whereIn('equipment_id', $totalPhysicalItems)
                        ->whereIn('reservation_id', function ($query) use ($requestedStart, $requestedEnd) {
                            $query->select('id')->from('reservations')
                                ->where('status', 'approved') // Only count confirmed ones
                                ->where('usage_date', '<', $requestedEnd)
                                ->whereRaw('DATE_ADD(usage_date, INTERVAL duration_hours HOUR) > ?', [$requestedStart]);
                        })->pluck('equipment_id')->toArray();

                    // 3. Calculate "Real" available IDs
                    $actuallyAvailableIds = array_diff($totalPhysicalItems, $busyIdsForThisItem);

                    // 4. Take only what is needed
                    $availableToAttach = array_slice($actuallyAvailableIds, 0, $quantity);

                    $idsToAttach = array_merge($idsToAttach, $availableToAttach);
                }
            }

            // --- THE FIX: STRICT STOCK CHECK ---
            if (count($idsToAttach) < $totalRequestedCount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Some requested equipment is unavailable for this time slot. Please reduce the quantity or change the time.');
            }
        }

        // 3. Save only if all checks pass
        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'room_id' => $validated['room_id'],
            'purpose' => $validated['purpose'],
            'submission_date' => now()->toDateString(),
            'usage_date' => $requestedStart,
            'duration_hours' => $durationHours,
            'status' => 'pending',
        ]);

        if (!empty($idsToAttach)) {
            $reservation->equipment()->attach($idsToAttach);
        }

        return redirect()->route('reservations.index')->with('status', 'Reservation request submitted!');
    }

    public function edit(Reservation $reservation)
    {
        // Security: Users can only edit their own pending requests
        if ($reservation->user_id !== Auth::id() || $reservation->status !== 'pending') {
            abort(403, 'You cannot edit this reservation.');
        }

        $rooms = Room::where('availability_status', 'available')->get();
        $rawEquipments = \App\Models\Equipment::where('status', 'available')->get();

        // Group equipment exactly like we did in 'create'
        $groupedEquipment = [];
        foreach ($rawEquipments as $eq) {
            if (!isset($groupedEquipment[$eq->category])) $groupedEquipment[$eq->category] = [];
            if (!isset($groupedEquipment[$eq->category][$eq->name])) $groupedEquipment[$eq->category][$eq->name] = 0;
            $groupedEquipment[$eq->category][$eq->name]++;
        }

        return view('reservations.edit', compact('reservation', 'rooms', 'groupedEquipment'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        // 1. Security Check
        if ($reservation->user_id !== Auth::id() || $reservation->status !== 'pending') {
            abort(403, 'You cannot edit this reservation.');
        }

        // 2. Validate
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'purpose' => 'required|string|max:255',
            'reservation_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'equipment_requests' => 'nullable|array',
        ]);

        // 3. Time Calculations
        $requestedStart = \Carbon\Carbon::parse($validated['reservation_date'] . ' ' . $validated['start_time']);
        $startTime = \Carbon\Carbon::parse($validated['start_time']);
        $endTime = \Carbon\Carbon::parse($validated['end_time']);
        $durationHours = max(1, $startTime->diffInHours($endTime));
        $requestedEnd = (clone $requestedStart)->addHours($durationHours);

        // 4. Overlap Check (Ignoring THIS specific reservation)
        $conflict = Reservation::where('room_id', $validated['room_id'])
            ->where('id', '!=', $reservation->id) // CRITICAL: Ignore self
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($requestedStart, $requestedEnd) {
                $query->where('usage_date', '<', $requestedEnd)
                    ->whereRaw('DATE_ADD(usage_date, INTERVAL duration_hours HOUR) > ?', [$requestedStart]);
            })->first();

        if ($conflict) {
            return redirect()->back()->withInput()->with('error', 'This room is already booked by someone else during this new time.');
        }

        // 5. Update Record
        $reservation->update([
            'room_id' => $validated['room_id'],
            'purpose' => $validated['purpose'],
            'usage_date' => $requestedStart,
            'duration_hours' => $durationHours,
        ]);

        // 6. Reset & Re-sync Equipment
        $reservation->equipment()->detach(); // Remove old equipment

        if (!empty($validated['equipment_requests'])) {
            $idsToAttach = [];
            foreach ($validated['equipment_requests'] as $itemName => $quantity) {
                if ($quantity > 0) {
                    $ids = \App\Models\Equipment::where('name', $itemName)
                        ->where('status', 'available')
                        ->take($quantity)
                        ->pluck('id')
                        ->toArray();
                    $idsToAttach = array_merge($idsToAttach, $ids);
                }
            }
            $reservation->equipment()->attach($idsToAttach);
        }

        return redirect()->route('reservations.index')->with('status', 'Reservation updated successfully!');
    }

    public function destroy(Reservation $reservation)
    {
        // Optional: Only allow deletion if status is 'pending'
        $reservation->delete();
        return redirect()->route('reservations.index')->with('success', 'Reservation cancelled.');
    }

    public function checkAvailableEquipment(Request $request)
    {
        $date = $request->query('date');
        $start = $request->query('start');
        $end = $request->query('end');

        if (!$date || !$start || !$end) return response()->json([]);

        $requestedStart = Carbon::parse($date . ' ' . $start);
        $requestedEnd = Carbon::parse($date . ' ' . $end);

        // 1. Get IDs of equipment currently tied to APPROVED reservations during this time
        $busyEquipmentIds = DB::table('equipment_reservation')
            ->whereIn('reservation_id', function ($query) use ($requestedStart, $requestedEnd) {
                $query->select('id')->from('reservations')
                    ->where('status', 'approved')
                    ->where('usage_date', '<', $requestedEnd)
                    ->whereRaw('DATE_ADD(usage_date, INTERVAL duration_hours HOUR) > ?', [$requestedStart]);
            })->pluck('equipment_id')->toArray();

        // 2. Get all physical items that are NOT in maintenance
        $allEquipment = \App\Models\Equipment::where('status', 'available')->get();

        // 3. Group them by category and name, but subtract the busy ones
        $availability = [];

        foreach ($allEquipment as $item) {
            if (!isset($availability[$item->category])) {
                $availability[$item->category] = [];
            }

            if (!isset($availability[$item->category][$item->name])) {
                $availability[$item->category][$item->name] = 0;
            }

            // Only count this specific item if its ID is NOT busy
            if (!in_array($item->id, $busyEquipmentIds)) {
                $availability[$item->category][$item->name]++;
            }
        }

        return response()->json($availability);
    }

    public function getBookedTimes(Request $request)
    {
        $roomId = $request->query('room_id');
        $date = $request->query('date');

        if (!$roomId || !$date) {
            return response()->json([]);
        }

        // Find all APPROVED reservations for this exact room on this exact date
        $reservations = Reservation::where('room_id', $roomId)
            ->where('status', 'approved')
            ->whereDate('usage_date', $date)
            ->get();

        $bookedSlots = $reservations->map(function ($res) {
            $start = \Carbon\Carbon::parse($res->usage_date);
            $end = (clone $start)->addHours($res->duration_hours);
            return [
                'start' => $start->format('H:i'),
                'end' => $end->format('H:i'),
            ];
        });

        return response()->json($bookedSlots);
    }
}
