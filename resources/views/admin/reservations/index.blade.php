<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Approval Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-4 text-green-600 bg-green-100 p-4 rounded-lg">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-lg">{{ session('error') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">

                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-gray-200 bg-gray-50 text-sm">
                                <th class="p-3">Requested By</th>
                                <th class="p-3">Schedule</th>
                                <th class="p-3">Room & Details</th>
                                <th class="p-3">Equipment Required</th>
                                <th class="p-3 text-center">Status / Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reservations as $reservation)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition {{ $reservation->status === 'pending' ? 'bg-yellow-50' : '' }}">

                                <td class="p-3">
                                    <div class="font-bold">{{ $reservation->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $reservation->user->email }}</div>
                                    <div class="text-xs text-indigo-500 mt-1">Requested: {{ $reservation->created_at->format('M d, Y h:i A') }}</div>
                                </td>

                                <td class="p-3">
                                    <div class="font-bold text-indigo-600">{{ \Carbon\Carbon::parse($reservation->usage_date)->format('M d, Y') }}</div>
                                    <div class="text-sm">
                                        {{ \Carbon\Carbon::parse($reservation->usage_date)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::parse($reservation->usage_date)->addHours($reservation->duration_hours)->format('H:i') }}
                                        <span class="text-xs text-gray-400">({{ $reservation->duration_hours }} hrs)</span>
                                    </div>
                                </td>

                                <td class="p-3">
                                    <div class="font-bold">{{ $reservation->room->building }} (Fl {{ $reservation->room->floor }})</div>
                                    <div class="text-sm text-gray-600 italic">"{{ $reservation->purpose }}"</div>
                                </td>

                                <td class="p-3">
                                    @if($reservation->equipment->count() > 0)
                                        <ul class="text-xs list-disc pl-4 text-gray-600">
                                            @foreach($reservation->equipment->groupBy('name') as $name => $items)
                                                <li><span class="font-bold">{{ $items->count() }}x</span> {{ $name }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-xs text-gray-400">No equipment</span>
                                    @endif
                                </td>

                                <td class="p-3 text-center">
                                    @if($reservation->status === 'pending')
                                        <div class="flex flex-col space-y-2 items-center">
                                            <form method="POST" action="{{ route('admin.reservations.approve', $reservation->id) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="w-24 bg-green-600 text-white text-xs font-bold px-3 py-1.5 rounded hover:bg-green-700">APPROVE</button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.reservations.reject', $reservation->id) }}" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="w-24 bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded hover:bg-red-700">REJECT</button>
                                            </form>
                                        </div>

                                        @elseif($reservation->status === 'approved')
                                        <div class="flex flex-col space-y-2 items-center">
                                            <span class="px-2 py-1 rounded text-xs font-bold uppercase bg-green-100 text-green-700 mb-2">
                                                Approved
                                            </span>

                                            @php
                                                // Calculate the exact end time of the reservation
                                                $endTime = \Carbon\Carbon::parse($reservation->usage_date)->addHours($reservation->duration_hours);
                                            @endphp

                                            @if($endTime->isPast())
                                                <form method="POST" action="{{ route('admin.reservations.done', $reservation->id) }}" onsubmit="return confirm('Confirm room/equipment has been returned safely?');">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="w-24 bg-blue-600 text-white text-xs font-bold px-3 py-1.5 rounded hover:bg-blue-700">MARK DONE</button>
                                                </form>
                                            @else
                                                <div class="text-[10px] text-gray-500 text-center italic mt-1 leading-tight">
                                                    In Progress / Upcoming<br>
                                                    <span class="font-semibold">Ends: {{ $endTime->format('M d, h:i A') }}</span>
                                                </div>
                                            @endif
                                        </div>

                                    @else
                                        <div class="flex flex-col items-center">
                                            <span class="px-2 py-1 rounded text-xs font-bold uppercase
                                                {{ $reservation->status === 'done' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                                {{ $reservation->status }}
                                            </span>

                                            @if($reservation->status === 'done' && $reservation->returned_at)
                                                <div class="text-[10px] text-gray-500 mt-2">
                                                    Returned:<br>
                                                    {{ $reservation->returned_at->format('M d, h:i A') }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                            </tr>
                            @empty
                            <tr><td colspan="5" class="p-4 text-center text-gray-500">No reservations found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $reservations->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>