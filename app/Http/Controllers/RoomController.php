<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rooms = Room::latest()->get();
        return view('rooms.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('rooms.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'building' => 'required|string|max:255',
            'floor' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'availability_status' => 'required|in:available,maintenance,unavailable',
        ]);

        Room::create($validated);
        return redirect()->route('rooms.index')->with('status', 'Room added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        return view('rooms.edit', compact('room'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'building' => 'required|string|max:255',
            'floor' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'availability_status' => 'required|in:available,maintenance,unavailable',
        ]);

        $room->update($validated);
        return redirect()->route('rooms.index')->with('status', 'Room updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(room $room)
    {
        $room->delete();
        return redirect()->route('rooms.index')->with('status', 'Room deleted!');
    }
}
