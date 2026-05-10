<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Equipment Management</h2>
            <a href="{{ route('equipments.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-semibold text-sm">+ Add Equipment</a>
        </div>
    </x-slot>

    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 text-green-600 bg-green-100 p-4 rounded-lg">{{ session('status') }}</div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-200 bg-gray-50">
                        <th class="p-3 w-10"></th> <th class="p-3">Item Name</th>
                        <th class="p-3">Category</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Quantity</th>
                    </tr>
                </thead>

                @forelse ($groupedEquipments as $groupKey => $items)
                    @php $first = $items->first(); @endphp

                    <tbody x-data="{ open: false }">

                        <tr @click="open = !open" class="border-b border-gray-100 hover:bg-gray-100 cursor-pointer transition-colors">
                            <td class="p-3 text-gray-400 text-center">
                                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                <svg x-show="open" style="display: none;" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </td>
                            <td class="p-3 font-bold text-gray-800">{{ $first->name }}</td>
                            <td class="p-3 text-gray-600">{{ $first->category }}</td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs font-bold capitalize {{ strtolower($first->status) === 'available' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $first->status }}
                                </span>
                            </td>
                            <td class="p-3 font-bold text-indigo-600 text-lg">
                                {{ $items->count() }}
                            </td>
                        </tr>

                        <tr x-show="open" style="display: none;" class="bg-gray-50 border-b border-gray-200">
                            <td colspan="5" class="p-4">
                                <div class="ml-10 bg-white border border-gray-200 rounded-md shadow-sm">
                                    <table class="w-full text-left text-sm">
                                        <thead>
                                            <tr class="bg-gray-100 border-b border-gray-200 text-gray-600">
                                                <th class="p-2 pl-4">Equipment Code</th>
                                                <th class="p-2 text-right pr-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($items as $item)
                                            <tr class="border-b border-gray-100 hover:bg-gray-50 last:border-none">
                                                <td class="p-2 pl-4 font-mono text-gray-700">{{ $item->code }}</td>
                                                <td class="p-2 pr-4 flex justify-end space-x-3 items-center">
                                                    <a href="{{ route('equipments.edit', $item) }}" class="text-blue-600 hover:underline text-xs font-semibold">Edit</a>
                                                    <span class="text-gray-300">|</span>
                                                    <form method="POST" action="{{ route('equipments.destroy', $item) }}" onsubmit="return confirm('Delete this specific equipment (Code: {{ $item->code }})?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:underline text-xs font-semibold">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody>
                        <tr><td colspan="5" class="p-3 text-center text-gray-500">No equipment has been added yet.</td></tr>
                    </tbody>
                @endforelse
            </table>
        </div></div>
    </div></div>
</x-app-layout>