<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Borrower Management</h2>
            <a href="{{ route('borrowers.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-semibold text-sm">+ Add Borrower</a>
        </div>
    </x-slot>

    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status')) <div class="mb-4 text-green-600 bg-green-100 p-4 rounded-lg">{{ session('status') }}</div> @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead><tr class="border-b-2 border-gray-200 bg-gray-50">
                    <th class="p-3">Name & Email</th><th class="p-3">NIM/NIK</th><th class="p-3">Account Type</th><th class="p-3">Phone</th><th class="p-3">Actions</th>
                </tr></thead>
                <tbody>
                    @forelse ($borrowers as $borrower)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-3">
                            <div class="font-bold text-gray-800">{{ $borrower->name }}</div>
                            <div class="text-sm text-gray-500">{{ $borrower->email }}</div>
                        </td>
                        <td class="p-3 text-gray-600">{{ $borrower->identity_number ?? 'N/A' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs font-bold bg-gray-100 text-gray-700 capitalize border border-gray-200">
                                {{ $borrower->account_type }}
                            </span>
                        </td>
                        <td class="p-3 text-gray-600">{{ $borrower->phone_number ?? 'N/A' }}</td>
                        <td class="p-3 flex space-x-2 items-center">
                            <a href="{{ route('borrowers.edit', $borrower) }}" class="text-blue-600 hover:underline text-sm">Edit</a>
                            <span class="text-gray-300">|</span>
                            <form method="POST" action="{{ route('borrowers.destroy', $borrower) }}" onsubmit="return confirm('Delete this borrower?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-3 text-center text-gray-500">No borrowers have been added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div></div>
</x-app-layout>