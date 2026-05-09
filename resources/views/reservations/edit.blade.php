<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Reservation Request</h2>
    </x-slot>

    @php
        // Break down the saved usage_date into components for the form inputs
        $currentDate = \Carbon\Carbon::parse($reservation->usage_date)->format('Y-m-d');
        $currentStartTime = \Carbon\Carbon::parse($reservation->usage_date)->format('H:i');
        $currentEndTime = \Carbon\Carbon::parse($reservation->usage_date)->addHours($reservation->duration_hours)->format('H:i');
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (session('error'))
                    <div class="mb-4 text-red-700 bg-red-100 p-4 rounded-lg">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('reservations.update', $reservation->id) }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="purpose" :value="__('Purpose of Reservation')" />
                        <x-text-input id="purpose" class="block mt-1 w-full" type="text" name="purpose" :value="old('purpose', $reservation->purpose)" required autofocus />
                        <x-input-error :messages="$errors->get('purpose')" class="mt-2" />
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="reservation_date" :value="__('Date')" />
                            <x-text-input id="reservation_date" class="block mt-1 w-full" type="date" name="reservation_date" :value="old('reservation_date', $currentDate)" required />
                            <x-input-error :messages="$errors->get('reservation_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="start_time" :value="__('Start Time')" />
                            <x-text-input id="start_time" class="block mt-1 w-full" type="time" name="start_time" :value="old('start_time', $currentStartTime)" required />
                            <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="end_time" :value="__('End Time')" />
                            <x-text-input id="end_time" class="block mt-1 w-full" type="time" name="end_time" :value="old('end_time', $currentEndTime)" required />
                            <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="room_id" :value="__('Select Room')" />
                        <select id="room_id" name="room_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id', $reservation->room_id) == $room->id ? 'selected' : '' }}>
                                    {{ $room->building }} - Floor {{ $room->floor }} (Capacity: {{ $room->capacity }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-1">{{ __('Update Equipment') }}</h3>
                        <p class="text-sm text-gray-500 mb-4">Please re-select any equipment you need for this updated reservation.</p>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div>
                                <x-input-label for="eq_category" :value="__('Category')" />
                                <select id="eq_category" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Select Category --</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="eq_name" :value="__('Item Name')" />
                                <select id="eq_name" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" disabled>
                                    <option value="">-- First Select Category --</option>
                                </select>
                            </div>
                            <div class="flex space-x-2">
                                <div class="w-20">
                                    <x-input-label for="eq_qty" :value="__('Qty')" />
                                    <x-text-input id="eq_qty" type="number" min="1" value="1" class="block mt-1 w-full" disabled />
                                </div>
                                <button type="button" id="add_eq_btn" class="mt-6 bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 disabled:opacity-50" disabled>Add</button>
                            </div>
                        </div>
                        <div id="selected_equipment_list" class="mt-4 space-y-2"></div>
                    </div>

                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('reservations.index') }}" class="text-gray-600 hover:underline mr-4">Cancel</a>
                        <x-primary-button>{{ __('Update Request') }}</x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const equipmentData = JSON.parse(JSON.stringify(@json($groupedEquipment)));
            const selectedCart = {};

            const catSelect = document.getElementById('eq_category');
            const nameSelect = document.getElementById('eq_name');
            const qtyInput = document.getElementById('eq_qty');
            const addBtn = document.getElementById('add_eq_btn');
            const listDiv = document.getElementById('selected_equipment_list');

            for (const category in equipmentData) {
                catSelect.add(new Option(category, category));
            }

            function refreshItemDropdown() {
                nameSelect.innerHTML = '<option value="">-- Select Item --</option>';
                const selectedCat = catSelect.value;
                if (selectedCat && equipmentData[selectedCat]) {
                    nameSelect.disabled = false;
                    let hasAvailableItems = false;
                    for (const itemName in equipmentData[selectedCat]) {
                        const availableQty = equipmentData[selectedCat][itemName];
                        if (availableQty > 0) {
                            nameSelect.add(new Option(`${itemName} (${availableQty} available)`, itemName));
                            hasAvailableItems = true;
                        }
                    }
                    if (!hasAvailableItems) {
                        nameSelect.innerHTML = '<option value="">-- All items selected --</option>';
                        nameSelect.disabled = true;
                    }
                } else {
                    nameSelect.disabled = true;
                    qtyInput.disabled = true;
                    addBtn.disabled = true;
                }
                qtyInput.value = '';
                qtyInput.disabled = true;
                addBtn.disabled = true;
            }

            catSelect.addEventListener('change', refreshItemDropdown);

            nameSelect.addEventListener('change', function() {
                if (this.value) {
                    qtyInput.disabled = false;
                    addBtn.disabled = false;
                    qtyInput.max = equipmentData[catSelect.value][this.value];
                    qtyInput.value = 1;
                } else {
                    qtyInput.disabled = true;
                    addBtn.disabled = true;
                }
            });

            function renderCart() {
                listDiv.innerHTML = '';
                for (const name in selectedCart) {
                    const item = selectedCart[name];
                    const row = document.createElement('div');
                    row.className = 'flex justify-between items-center bg-indigo-50 text-indigo-700 px-4 py-2 rounded border border-indigo-100 mt-2';
                    row.innerHTML = `
                        <span><span class="font-bold">${item.qty}x</span> ${name} <span class="text-xs text-indigo-400">(${item.category})</span></span>
                        <input type="hidden" name="equipment_requests[${name}]" value="${item.qty}">
                        <button type="button" class="text-red-500 hover:text-red-700 font-bold text-sm remove-btn">Remove</button>
                    `;
                    row.querySelector('.remove-btn').addEventListener('click', function() {
                        equipmentData[item.category][name] += item.qty;
                        delete selectedCart[name];
                        renderCart();
                        refreshItemDropdown();
                    });
                    listDiv.appendChild(row);
                }
            }

            addBtn.addEventListener('click', function() {
                const category = catSelect.value;
                const name = nameSelect.value;
                const qty = parseInt(qtyInput.value);
                const availableQty = equipmentData[category][name];

                if (!name || isNaN(qty) || qty < 1 || qty > availableQty) {
                    alert(`Please enter a valid quantity between 1 and ${availableQty}.`);
                    return;
                }

                equipmentData[category][name] -= qty;
                if (selectedCart[name]) {
                    selectedCart[name].qty += qty;
                } else {
                    selectedCart[name] = { category: category, qty: qty };
                }
                renderCart();
                refreshItemDropdown();
            });
        });
    </script>
</x-app-layout>