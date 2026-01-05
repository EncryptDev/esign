<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Document') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Try simple upload first, fallback to original if needed --}}
                    @livewire('simple-upload-document')
                    {{-- @livewire('upload-document') --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
