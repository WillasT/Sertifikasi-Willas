<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Borrower Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-4 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-gray-200 bg-gray-50">
                                <th class="p-3">Name</th>
                                <th class="p-3">NIM/NIK</th>
                                <th class="p-3">Type</th>
                                <th class="p-3">Phone</th>
                                <th class="p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($borrowers as $borrower)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3">{{ $borrower->name }}<br><span class="text-sm text-gray-500">{{ $borrower->email }}</span></td>
                                    <td class="p-3">{{ $borrower->identity_number }}</td>
                                    <td class="p-3 capitalize">{{ $borrower->account_type }}</td>
                                    <td class="p-3">{{ $borrower->phone_number }}</td>
                                    <td class="p-3 flex space-x-2">
                                        <a href="{{ route('borrowers.edit', $borrower) }}" class="text-blue-600 hover:underline">Edit</a>

                                        <form method="POST" action="{{ route('borrowers.destroy', $borrower) }}" onsubmit="return confirm('Are you sure you want to delete this borrower?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>