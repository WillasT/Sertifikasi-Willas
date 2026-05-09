<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Admin Approval Dashboard
            </h2>

            <div class="flex space-x-3">
                <a href="{{ route('admin.reservations.export.excel') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow text-sm transition">
                    Export Excel
                </a>
                <a href="{{ route('admin.reservations.export.pdf') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow text-sm transition">
                    Export PDF
                </a>
            </div>
        </div>
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
                                <th class="p-3 text-center">Status</th>
                                <th class="p-3 text-center">Actions</th>
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
                                    <div class="font-bold text-md">{{ $reservation->room->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $reservation->room->building }} (Floor {{ $reservation->room->floor }})</div>

                                    <div class="text-sm text-gray-600 italic mt-1">"{{ $reservation->purpose }}"</div>
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
                                    @php
                                        $badgeColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'approved' => 'bg-green-100 text-green-800 border-green-200',
                                            'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                            'done' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        ];
                                        $colorClass = $badgeColors[$reservation->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                    @endphp
                                    <span class="px-3 py-1 rounded-full border text-xs font-bold uppercase tracking-wider {{ $colorClass }}">
                                        {{ $reservation->status }}
                                    </span>
                                </td>

                                <td class="p-3 text-center">
                                    <a href="{{ route('admin.reservations.show', $reservation->id) }}" class="inline-block bg-indigo-600 text-white text-xs font-bold px-4 py-2 rounded hover:bg-indigo-700 transition shadow-sm">
                                        DETAILS
                                    </a>
                                </td>

                            </tr>
                            @empty
                            <tr><td colspan="6" class="p-4 text-center text-gray-500">No reservations found.</td></tr>
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