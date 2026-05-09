<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Reservations</h2>
            <a href="{{ route('reservations.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-semibold text-sm">+ New Request</a>
        </div>
    </x-slot>

    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status')) <div class="mb-4 text-green-600 bg-green-100 p-4 rounded-lg">{{ session('status') }}</div> @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead><tr class="border-b-2 border-gray-200 bg-gray-50">
                    <th class="p-3">Date & Time</th><th class="p-3">Room</th><th class="p-3">Purpose</th><th class="p-3">Status</th>
                </tr></thead>
                <tbody>
                    @forelse ($reservations as $reservation)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-3 font-medium">
                            {{ \Carbon\Carbon::parse($reservation->usage_date)->format('M d, Y') }} <br>
                            <span class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($reservation->usage_date)->format('H:i') }}
                                ({{ $reservation->duration_hours }} hours)
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-md text-gray-800">
                                {{ $reservation->room->name }}
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $reservation->room->building }} (Floor {{ $reservation->room->floor }})
                            </div>
                        </td>
                        <td class="p-3">{{ $reservation->purpose }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase
                                {{ $reservation->status === 'approved' ? 'bg-green-100 text-green-700' : ($reservation->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ $reservation->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-3 text-center text-gray-500">You haven't made any reservations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div></div>
</x-app-layout>
