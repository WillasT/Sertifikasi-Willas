<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reservation Details #{{ $reservation->id }}
            </h2>
            <a href="{{ route('admin.reservations.index') }}" class="text-gray-600 hover:text-gray-900 font-semibold text-sm">
                &larr; Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="text-green-600 bg-green-100 p-4 rounded-lg">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="text-red-600 bg-red-100 p-4 rounded-lg">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Requester Information</h3>
                    <div class="grid grid-cols-3 gap-y-3 text-sm">
                        <div class="font-semibold text-gray-600">Name:</div>
                        <div class="col-span-2">{{ $reservation->user->name }}</div>

                        <div class="font-semibold text-gray-600">Email:</div>
                        <div class="col-span-2 text-indigo-600">{{ $reservation->user->email }}</div>

                        <div class="font-semibold text-gray-600">Identity Number:</div>
                        <div class="col-span-2">{{ $reservation->user->identity_number ?? 'N/A' }}</div>

                        <div class="font-semibold text-gray-600">Phone:</div>
                        <div class="col-span-2">{{ $reservation->user->phone_number ?? 'N/A' }}</div>

                        <div class="font-semibold text-gray-600">Account Type:</div>
                        <div class="col-span-2 capitalize">{{ $reservation->user->account_type ?? 'Standard' }}</div>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Room & Schedule Details</h3>
                    <div class="grid grid-cols-3 gap-y-3 text-sm">
                        <div class="font-semibold text-gray-600">Schedule:</div>
                        <div class="col-span-2 font-bold text-indigo-600">
                            {{ \Carbon\Carbon::parse($reservation->usage_date)->format('F j, Y') }} <br>
                            {{ \Carbon\Carbon::parse($reservation->usage_date)->format('h:i A') }} -
                            {{ \Carbon\Carbon::parse($reservation->usage_date)->addHours($reservation->duration_hours)->format('h:i A') }}
                            <span class="text-gray-400 font-normal">({{ $reservation->duration_hours }} hours)</span>
                        </div>

                        <div class="font-semibold text-gray-600 mt-2">Room Name:</div>
                        <div class="col-span-2 mt-2 font-bold">{{ $reservation->room->name }}</div>

                        <div class="font-semibold text-gray-600">Location:</div>
                        <div class="col-span-2">{{ $reservation->room->building }} (Floor {{ $reservation->room->floor }})</div>

                        <div class="font-semibold text-gray-600">Capacity:</div>
                        <div class="col-span-2">{{ $reservation->room->capacity }} people</div>

                        <div class="font-semibold text-gray-600">Room Status:</div>
                        <div class="col-span-2 capitalize">{{ $reservation->room->availability_status }}</div>

                        <div class="font-semibold text-gray-600 mt-2">Purpose:</div>
                        <div class="col-span-2 mt-2 italic text-gray-700">"{{ $reservation->purpose }}"</div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Equipment Required</h3>

                @if($reservation->equipment->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="p-2">Item Name</th>
                                    <th class="p-2">Code</th>
                                    <th class="p-2">Category</th>
                                    <th class="p-2 text-center">Qty Requested</th>
                                    <th class="p-2 text-center">Total Stock Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reservation->equipment->groupBy('id') as $id => $items)
                                    @php $item = $items->first(); @endphp
                                    <tr class="border-b border-gray-100">
                                        <td class="p-2 font-bold">{{ $item->name }}</td>
                                        <td class="p-2">{{ $item->code ?? 'N/A' }}</td>
                                        <td class="p-2">{{ $item->category ?? 'N/A' }}</td>
                                        <td class="p-2 text-center font-bold text-indigo-600">{{ $items->count() }}</td>
                                        <td class="p-2 text-center">{{ $item->stock_quantity ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No additional equipment requested for this reservation.</p>
                @endif
            </div>

            <div class="bg-gray-50 p-6 shadow-sm rounded-lg border border-gray-200 flex flex-col md:flex-row items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Reservation Status:
                        <span class="uppercase text-indigo-600">{{ $reservation->status }}</span>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">Requested on: {{ $reservation->created_at->format('M d, Y h:i A') }}</p>
                </div>

                <div class="mt-4 md:mt-0 flex space-x-3">
                    @if($reservation->status === 'pending')
                        <form method="POST" action="{{ route('admin.reservations.approve', $reservation->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="bg-green-600 text-white font-bold px-6 py-2 rounded shadow hover:bg-green-700 transition">
                                APPROVE REQUEST
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.reservations.reject', $reservation->id) }}" onsubmit="return confirm('Are you sure you want to reject this request?');">
                            @csrf @method('PATCH')
                            <button type="submit" class="bg-red-600 text-white font-bold px-6 py-2 rounded shadow hover:bg-red-700 transition">
                                REJECT REQUEST
                            </button>
                        </form>

                    @elseif($reservation->status === 'approved')
                        @php
                            $endTime = \Carbon\Carbon::parse($reservation->usage_date)->addHours($reservation->duration_hours);
                        @endphp

                        @if($endTime->isPast())
                            <form method="POST" action="{{ route('admin.reservations.done', $reservation->id) }}" onsubmit="return confirm('Confirm room/equipment has been returned safely?');">
                                @csrf @method('PATCH')
                                <button type="submit" class="bg-blue-600 text-white font-bold px-6 py-2 rounded shadow hover:bg-blue-700 transition">
                                    MARK AS DONE (ITEMS RETURNED)
                                </button>
                            </form>
                        @else
                            <div class="bg-white border px-4 py-2 rounded text-sm text-gray-500 shadow-sm text-center">
                                Event in Progress/Upcoming <br>
                                <span class="font-bold">Ends: {{ $endTime->format('M d, Y h:i A') }}</span>
                            </div>
                        @endif
                    @elseif($reservation->status === 'done' && $reservation->returned_at)
                        <div class="bg-white border px-4 py-2 rounded text-sm text-gray-700 shadow-sm text-center">
                            <strong>Completed</strong><br>
                            Returned at: {{ $reservation->returned_at->format('M d, Y h:i A') }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>