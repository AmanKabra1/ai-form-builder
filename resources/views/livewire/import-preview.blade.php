<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Import from Word / Excel</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            {{-- Upload Zone --}}
            @if(!$job)
                <div class="bg-white rounded-xl shadow p-8">
                    <h3 class="font-semibold text-gray-700 mb-4">Upload a .docx or .xlsx file</h3>

                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-10 text-center hover:border-indigo-400 transition-colors">
                        <input type="file" wire:model="uploadedFile" accept=".docx,.xlsx" id="importFile"
                               class="hidden" />
                        <label for="importFile" class="cursor-pointer">
                            <div class="text-5xl mb-3">📂</div>
                            <p class="text-gray-600 font-medium">Click to select file</p>
                            <p class="text-gray-400 text-sm mt-1">Supported: .docx, .xlsx (max 10MB)</p>
                        </label>
                        @if($uploadedFile)
                            <p class="mt-3 text-indigo-600 text-sm font-medium">
                                Selected: {{ $uploadedFile->getClientOriginalName() }}
                            </p>
                        @endif
                    </div>

                    @error('uploadedFile')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror

                    <div class="mt-6 flex gap-3">
                        <button wire:click="upload" wire:loading.attr="disabled"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 font-medium">
                            <span wire:loading>Uploading...</span>
                            <span wire:loading.remove>Parse File</span>
                        </button>
                        <a href="{{ route('forms.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                    </div>

                    <div class="mt-6 text-xs text-gray-500 space-y-1">
                        <p><strong>Word (.docx):</strong> Headings become sections, questions become fields, bullet lists become options.</p>
                        <p><strong>Excel (.xlsx):</strong> First row headers become field labels, types are auto-detected.</p>
                    </div>
                </div>
            @elseif($status === 'processing' || $status === 'pending')
                <div class="bg-white rounded-xl shadow p-12 text-center"
                     x-data="{ }"
                     x-init="setInterval(() => $wire.pollStatus(), 2000)">
                    <div class="text-5xl mb-4 animate-pulse">⚙️</div>
                    <h3 class="font-semibold text-gray-700 text-lg">Parsing your file...</h3>
                    <p class="text-gray-500 text-sm mt-2">This usually takes a few seconds.</p>
                </div>

            @elseif($status === 'failed')
                <div class="bg-white rounded-xl shadow p-8">
                    <div class="text-red-500 font-medium mb-2">✗ Parsing failed</div>
                    <p class="text-gray-600 text-sm">{{ $error }}</p>
                    <button wire:click="$set('job', null)" class="mt-4 text-indigo-600 hover:underline text-sm">Try again</button>
                </div>

            @elseif($status === 'done')
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="p-6 border-b flex items-center justify-between">
                        <h3 class="font-semibold text-gray-700">
                            {{ count($fields) }} fields detected — review and confirm
                        </h3>
                        <button wire:click="confirmImport"
                                class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium text-sm">
                            ✓ Import as New Form
                        </button>
                    </div>

                    <div class="divide-y">
                        @foreach($fields as $i => $field)
                            <div class="px-6 py-3 flex items-center gap-3 flex-wrap">
                                <div class="flex-1 min-w-48">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-medium text-sm text-gray-800 truncate">{{ $field['label'] }}</span>
                                        @if(!empty($field['options']))
                                            <span class="text-xs text-gray-400 whitespace-nowrap">{{ count($field['options']) }} options</span>
                                        @endif
                                    </div>
                                </div>
                                <select wire:change="updateFieldType({{ $i }}, $event.target.value)"
                                        class="border border-gray-200 rounded-lg px-2 py-1 text-sm shrink-0 pr-8 min-w-32">
                                    @foreach(['text','textarea','number','email','phone','date','dropdown','radio','checkbox','file','heading','rating'] as $t)
                                        <option value="{{ $t }}" {{ $field['type'] === $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
