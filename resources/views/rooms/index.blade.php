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
                <thead>
                    <tr class="border-b-2 border-gray-200 bg-gray-50 text-sm text-left">
                        <th class="p-3">Room Name</th> <th class="p-3">Location</th>
                        <th class="p-3 text-center">Capacity</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rooms as $room)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="p-3 font-bold text-gray-800">
                            {{ $room->name }}
                        </td>

                        <td class="p-3">
                            {{ $room->building }}
                            <span class="text-xs text-gray-500">(Floor {{ $room->floor }})</span>
                        </td>

                        <td class="p-3 text-center">{{ $room->capacity }}</td>

                        <td class="p-3 text-center">
                            <span class="px-2 py-1 text-xs font-bold rounded {{ $room->availability_status === 'available' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($room->availability_status) }}
                            </span>
                        </td>

                        <td class="p-3 text-center">
                            </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div></div>
    </div></div>
</x-app-layout>