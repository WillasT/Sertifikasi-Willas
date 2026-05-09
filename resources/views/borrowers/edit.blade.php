<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Borrower: ') }} {{ $borrower->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('borrowers.update', $borrower) }}">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $borrower->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $borrower->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="identity_number" :value="__('NIM / NIK')" />
                        <x-text-input id="identity_number" class="block mt-1 w-full" type="text" name="identity_number" :value="old('identity_number', $borrower->identity_number)" required />
                        <x-input-error :messages="$errors->get('identity_number')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="phone_number" :value="__('Phone Number')" />
                        <x-text-input id="phone_number" class="block mt-1 w-full" type="tel" name="phone_number" :value="old('phone_number', $borrower->phone_number)" required oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                        <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="account_type" :value="__('Account Type')" />
                        <select id="account_type" name="account_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="student" {{ $borrower->account_type === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="lecturer" {{ $borrower->account_type === 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                        </select>
                        <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('borrowers.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                        <x-primary-button>
                            {{ __('Update Borrower') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>