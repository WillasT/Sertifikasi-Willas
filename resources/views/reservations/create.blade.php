<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Request a Reservation</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if (session('status'))
                    <div class="mb-4 text-green-600 bg-green-100 p-4 rounded-lg">{{ session('status') }}</div>
                @endif

                @if (session('error'))
                    <div class="mb-4 text-red-700 bg-red-100 p-4 rounded-lg flex justify-between items-center">
                        <span>{{ session('error') }}</span>
                        @if (session('error_link'))
                            <a href="{{ session('error_link') }}" class="underline font-bold hover:text-red-900">
                                Edit Existing Reservation &rarr;
                            </a>
                        @endif
                    </div>
                @endif
                <form method="POST" action="{{ route('reservations.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="purpose" :value="__('Purpose of Reservation')" />
                        <x-text-input id="purpose" class="block mt-1 w-full" type="text" name="purpose" :value="old('purpose')" required autofocus placeholder="e.g., Guest Lecture, Organization Meeting" />
                        <x-input-error :messages="$errors->get('purpose')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="room_id" :value="__('Select Room')" />
                        <select id="room_id" name="room_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option value="">-- Choose a Room --</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                    {{ $room->name }} ({{ $room->building }}, Floor {{ $room->floor }} - Cap: {{ $room->capacity }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="reservation_date" :value="__('Date')" />
                            <x-text-input id="reservation_date" class="block mt-1 w-full" type="date" name="reservation_date" :value="old('reservation_date')" required />
                            <x-input-error :messages="$errors->get('reservation_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="start_time" :value="__('Start Time')" />
                            <x-text-input id="start_time" class="block mt-1 w-full" type="time" name="start_time" :value="old('start_time')" required />
                            <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="end_time" :value="__('End Time')" />
                            <x-text-input id="end_time" class="block mt-1 w-full" type="time" name="end_time" :value="old('end_time')" required />
                            <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                        </div>
                    </div>

                    <div id="time_warning_container" class="mt-2 hidden text-sm font-bold text-red-600 bg-red-100 p-3 rounded">
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('Request Equipment (Optional)') }}</h3>

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
                                <button type="button" id="add_eq_btn" class="mt-6 bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 disabled:opacity-50" disabled>
                                    Add
                                </button>
                            </div>
                        </div>

                        <div id="selected_equipment_list" class="mt-4 space-y-2"></div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            let equipmentData = {}; // Starts empty, fills up when times are picked
                            const selectedCart = {};

                            const dateInput = document.getElementById('reservation_date');
                            const startInput = document.getElementById('start_time');
                            const endInput = document.getElementById('end_time');

                            const catSelect = document.getElementById('eq_category');
                            const nameSelect = document.getElementById('eq_name');
                            const qtyInput = document.getElementById('eq_qty');
                            const addBtn = document.getElementById('add_eq_btn');
                            const listDiv = document.getElementById('selected_equipment_list');

                            const roomInput = document.getElementById('room_id');
                            const timeWarningContainer = document.getElementById('time_warning_container');
                            const submitBtn = document.getElementById('submit_reservation_btn');

                            let lockedSlots = [];

                            // NEW: Fetch live equipment availability when date/time changes
                            async function fetchLiveAvailability() {
                                const date = dateInput.value;
                                const start = startInput.value;
                                const end = endInput.value;

                                // Only fetch if all time fields are filled out
                                if (!date || !start || !end) return;

                                try {
                                    const response = await fetch(`/reservations/check-equipment?date=${date}&start=${start}&end=${end}`);
                                    equipmentData = await response.json();

                                    // Clear the dropdowns and rebuild categories
                                    catSelect.innerHTML = '<option value="">-- Select Category --</option>';
                                    nameSelect.innerHTML = '<option value="">-- First Select Category --</option>';

                                    for (const category in equipmentData) {
                                        catSelect.add(new Option(category, category));
                                    }

                                    // If user changed time, warn them that their current cart might be invalid
                                    if (Object.keys(selectedCart).length > 0) {
                                        alert('You changed the time slot. Please re-select your equipment to ensure availability.');
                                        for (let key in selectedCart) delete selectedCart[key];
                                        renderCart();
                                    }

                                    refreshItemDropdown();

                                } catch (error) {
                                    console.error("Failed to load equipment", error);
                                }
                            }

                            async function fetchBookedTimes() {
                                const roomId = roomInput.value;
                                const date = dateInput.value;

                                if (!roomId || !date) return;

                                try {
                                    const response = await fetch(`/reservations/booked-times?room_id=${roomId}&date=${date}`);
                                    lockedSlots = await response.json();
                                    checkTimeConflicts(); // Re-validate times as soon as we fetch the data
                                } catch (error) {
                                    console.error("Failed to load booked times", error);
                                }
                            }

                            function checkTimeConflicts() {
                                const start = startInput.value;
                                const end = endInput.value;

                                // Reset warning and unlock button
                                timeWarningContainer.classList.add('hidden');
                                timeWarningContainer.innerHTML = '';
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');

                                // If there are locked slots, show them a helpful list
                                if (lockedSlots.length > 0 && startInput.value === '') {
                                    let slotsHtml = '<strong>Note:</strong> This room is already booked today during these times: <ul class="list-disc pl-5 mt-1">';
                                    lockedSlots.forEach(slot => {
                                        slotsHtml += `<li>${slot.start} to ${slot.end}</li>`;
                                    });
                                    slotsHtml += '</ul>';
                                    timeWarningContainer.innerHTML = slotsHtml;
                                    timeWarningContainer.classList.remove('hidden');
                                    timeWarningContainer.classList.replace('text-red-600', 'text-yellow-700');
                                    timeWarningContainer.classList.replace('bg-red-100', 'bg-yellow-100');
                                }

                                if (!start || !end) return;

                                // Convert times to comparable minutes (e.g., 08:30 -> 510)
                                const userStartMinutes = timeToMinutes(start);
                                const userEndMinutes = timeToMinutes(end);

                                let hasConflict = false;

                                lockedSlots.forEach(slot => {
                                    const slotStartMinutes = timeToMinutes(slot.start);
                                    const slotEndMinutes = timeToMinutes(slot.end);

                                    // Conflict formula: User starts before slot ends AND User ends after slot starts
                                    if (userStartMinutes < slotEndMinutes && userEndMinutes > slotStartMinutes) {
                                        hasConflict = true;
                                    }
                                });

                                if (hasConflict) {
                                    // Lock the submit button and show a red error
                                    submitBtn.disabled = true;
                                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

                                    timeWarningContainer.innerHTML = `<strong>Conflict:</strong> Your selected time (${start} - ${end}) overlaps with an already approved booking for this room. Please choose a different time or room.`;
                                    timeWarningContainer.classList.remove('hidden');
                                    timeWarningContainer.classList.replace('text-yellow-700', 'text-red-600');
                                    timeWarningContainer.classList.replace('bg-yellow-100', 'bg-red-100');
                                }
                            }

                            // Listen for time changes
                            dateInput.addEventListener('change', fetchLiveAvailability);
                            startInput.addEventListener('change', fetchLiveAvailability);
                            endInput.addEventListener('change', fetchLiveAvailability);

                            function timeToMinutes(timeStr) {
                                const [hours, minutes] = timeStr.split(':').map(Number);
                                return (hours * 60) + minutes;
                            }

                            // 3. Attach Event Listeners
                            roomInput.addEventListener('change', fetchBookedTimes);
                            dateInput.addEventListener('change', fetchBookedTimes);

                            // Check for conflicts whenever the user tweaks the start or end time
                            startInput.addEventListener('change', checkTimeConflicts);
                            endInput.addEventListener('change', checkTimeConflicts);

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
                                if (selectedCart[name]) selectedCart[name].qty += qty;
                                else selectedCart[name] = { category: category, qty: qty };

                                renderCart();
                                refreshItemDropdown();
                            });
                        });
                    </script>

                    <div class="flex items-center justify-end mt-8">
                        <x-primary-button id="submit_reservation_btn">{{ __('Submit Request') }}</x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>