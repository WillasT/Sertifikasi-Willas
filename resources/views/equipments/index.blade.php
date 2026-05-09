<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Equipment Management</h2>
            <a href="{{ route('equipments.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-semibold">Add Equipment</a>
        </div>
    </x-slot>

    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status')) <div class="mb-4 text-green-600 bg-green-100 p-4 rounded-lg">{{ session('status') }}</div> @endif
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead><tr class="border-b-2 border-gray-200 bg-gray-50">
                    <th class="p-3">Item Name</th><th class="p-3">Code</th><th class="p-3">Category</th><th class="p-3">Status</th><th class="p-3">Actions</th>
                </tr></thead>
                <tbody>
                    @foreach ($equipments as $equipment)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-3 font-medium">{{ $equipment->name }}</td>
                        <td class="p-3">{{ $equipment->code }}</td>
                        <td class="p-3">{{ $equipment->category }}</td>
                        <td class="p-3 capitalize">{{ $equipment->status }}</td>
                        <td class="p-3 flex space-x-2">
                            <a href="{{ route('equipments.edit', $equipment) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('equipments.destroy', $equipment) }}" onsubmit="return confirm('Delete this item?');">
                                @csrf @method('DELETE') <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div></div>
    </div></div>
</x-app-layout>