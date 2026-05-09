<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class BorrowerController extends Controller
{
    // READ: View all borrowers (students & lecturers)
    public function index()
    {
        // We exclude 'admin' accounts from this list
        $borrowers = User::whereIn('account_type', ['student', 'lecturer'])->latest()->get();
        return view('borrowers.index', compact('borrowers'));
    }

    public function create()
    {
        return view('borrowers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'], // We need a password for new users
            'identity_number' => ['required', 'string', 'max:50', 'unique:users,identity_number'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20'],
            'account_type' => ['required', 'in:student,lecturer'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'identity_number' => $request->identity_number,
            'phone_number' => $request->phone_number,
            'account_type' => $request->account_type,
        ]);

        return redirect()->route('borrowers.index')->with('status', 'Borrower added successfully!');
    }

    // UPDATE: Show the edit form
    public function edit(User $borrower)
    {
        return view('borrowers.edit', compact('borrower'));
    }

    // UPDATE: Save the changes to the database
    public function update(Request $request, User $borrower)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($borrower->id)],
            'identity_number' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($borrower->id)],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20'],
            'account_type' => ['required', 'in:student,lecturer'],
        ]);

        $borrower->update($request->only('name', 'email', 'identity_number', 'phone_number', 'account_type'));

        return redirect()->route('borrowers.index')->with('status', 'Borrower updated successfully!');
    }

    // DELETE: Remove the borrower from the system
    public function destroy(User $borrower)
    {
        $borrower->delete();
        return redirect()->route('borrowers.index')->with('status', 'Borrower deleted successfully!');
    }
}