<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $equipments = Equipment::latest()->get();
        return view('equipments.index', compact('equipments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('equipments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:equipment,code'],
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'status' => 'required|in:available,borrowed,maintenance',
        ]);

        Equipment::create($validated);
        return redirect()->route('equipments.index')->with('status', 'Equipment added successfully!');
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
    public function edit(Equipment $equipment)
    {
        return view('equipments.edit', compact('equipment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', Rule::unique('equipment')->ignore($equipment->id)],
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'status' => 'required|in:available,borrowed,maintenance',
        ]);

        $equipment->update($validated);
        return redirect()->route('equipments.index')->with('status', 'Equipment updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipment $equipment)
    {
        $equipment->delete();
        return redirect()->route('equipments.index')->with('status', 'Equipment deleted!');
    }
}
