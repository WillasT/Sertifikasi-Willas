<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Room Management</h2>
            <a href="{{ route('rooms.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-semibold">Add New Room</a>
        </div>
    </x-slot>

    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status')) <div class="mb-4 text-green-600 bg-green-100 p-4 rounded-lg">{{ session('status') }}</div> @endif
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead><tr class="border-b-2 border-gray-200 bg-gray-50">
                    <th class="p-3">Building & Floor</th><th class="p-3">Capacity</th><th class="p-3">Status</th><th class="p-3">Actions</th>
                </tr></thead>
                <tbody>
                    @foreach ($rooms as $room)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-3 font-bold">{{ $room->building }} <br> <span class="text-sm font-normal text-gray-500">Floor {{ $room->floor }}</span></td>
                        <td class="p-3">{{ $room->capacity }}</td>
                        <td class="p-3 capitalize">{{ $room->availability_status }}</td>
                        <td class="p-3 flex space-x-2">
                            <a href="{{ route('rooms.edit', $room) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('rooms.destroy', $room) }}" onsubmit="return confirm('Delete this room?');">
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