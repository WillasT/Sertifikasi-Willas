<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Room: ') }} {{ $room->building }} - Floor {{ $room->floor }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('rooms.update', $room) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <x-input-label for="name" :value="__('Room Name (e.g. Auditorium)')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $room->name ?? '')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="building" :value="__('Building Name')" />
                        <x-text-input id="building" class="block mt-1 w-full" type="text" name="building" :value="old('building', $room->building)" required autofocus />
                        <x-input-error :messages="$errors->get('building')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="floor" :value="__('Floor')" />
                        <x-text-input id="floor" class="block mt-1 w-full" type="text" name="floor" :value="old('floor', $room->floor)" required />
                        <x-input-error :messages="$errors->get('floor')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="capacity" :value="__('Capacity (Number of People)')" />
                        <x-text-input id="capacity" class="block mt-1 w-full" type="number" name="capacity" :value="old('capacity', $room->capacity)" required min="1" />
                        <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="availability_status" :value="__('Status')" />
                        <select id="availability_status" name="availability_status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="available" {{ old('availability_status', $room->availability_status) === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="maintenance" {{ old('availability_status', $room->availability_status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="unavailable" {{ old('availability_status', $room->availability_status) === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                        </select>
                        <x-input-error :messages="$errors->get('availability_status')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('rooms.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                        <x-primary-button>{{ __('Update Room') }}</x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>