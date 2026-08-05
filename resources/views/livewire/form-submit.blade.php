<div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-2xl mx-auto">

        @if($submitted)
            <div class="bg-white rounded-2xl shadow p-10 text-center">
                <div class="text-6xl mb-4">✅</div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Thank you!</h2>
                <p class="text-gray-500">{{ $successMsg }}</p>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow overflow-hidden">
                {{-- Form Header --}}
                <div class="bg-indigo-600 px-8 py-6">
                    <h1 class="text-2xl font-bold text-white">{{ $form->title }}</h1>
                    @if($form->description)
                        <p class="text-indigo-100 mt-1 text-sm">{{ $form->description }}</p>
                    @endif
                </div>

                {{-- Form Body --}}
                <form wire:submit="submit" class="p-8 space-y-5">

                    @foreach($form->fields as $field)
                        @php $key = $field['key']; $type = $field['type']; @endphp

                        @if($type === 'heading')
                            <div class="pt-4 border-t border-gray-100 {{ !$loop->first ? 'mt-6' : '' }}">
                                <h3 class="text-base font-bold text-gray-700">{{ $field['label'] }}</h3>
                            </div>
                            @continue
                        @endif

                        <div wire:key="field-{{ $key }}">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $field['label'] }}
                                @if($field['required'] ?? false)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>

                            @if(!empty($field['help_text']))
                                <p class="text-xs text-gray-500 mb-1">{{ $field['help_text'] }}</p>
                            @endif

                            @switch($type)
                                @case('textarea')
                                    <textarea wire:model="answers.{{ $key }}" rows="3"
                                              placeholder="{{ $field['placeholder'] ?? '' }}"
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200"></textarea>
                                    @break
                                @case('dropdown')
                                    <select wire:model="answers.{{ $key }}"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200">
                                        <option value="">-- Select --</option>
                                        @foreach($field['options'] ?? [] as $opt)
                                            <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @break
                                @case('radio')
                                    <div class="space-y-2 mt-1">
                                        @foreach($field['options'] ?? [] as $opt)
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" wire:model="answers.{{ $key }}"
                                                       value="{{ $opt['value'] }}"
                                                       class="text-indigo-600 border-gray-300 shrink-0" />
                                                <span class="text-sm text-gray-700">{{ $opt['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @break
                                @case('checkbox')
                                    <div class="space-y-2 mt-1">
                                        @foreach($field['options'] ?? [] as $opt)
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" wire:model="answers.{{ $key }}"
                                                       value="{{ $opt['value'] }}"
                                                       class="rounded text-indigo-600 border-gray-300 shrink-0" />
                                                <span class="text-sm text-gray-700">{{ $opt['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @break
                                @case('rating')
                                    <div class="flex gap-2 mt-1" x-data="{ val: @json($answers[$key] ?? 0), hover: 0 }">
                                        @for($i = 1; $i <= 5; $i++)
                                            <label class="cursor-pointer"
                                                   @mouseenter="hover = {{ $i }}" @mouseleave="hover = 0">
                                                <input type="radio" wire:model="answers.{{ $key }}"
                                                       value="{{ $i }}" class="sr-only"
                                                       @change="val = {{ $i }}" />
                                                <span class="text-2xl transition-colors"
                                                      :class="(hover || val) >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'">★</span>
                                            </label>
                                        @endfor
                                    </div>
                                    @break
                                @case('file')
                                    <input type="file" wire:model="answers.{{ $key }}"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                    @break
                                @case('date')
                                    <input type="date" wire:model="answers.{{ $key }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200" />
                                    @break
                                @case('number')
                                    <input type="number" wire:model="answers.{{ $key }}"
                                           placeholder="{{ $field['placeholder'] ?? '' }}"
                                           min="{{ $field['validation']['min'] ?? '' }}"
                                           max="{{ $field['validation']['max'] ?? '' }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200" />
                                    @break
                                @default
                                    <input type="{{ $type === 'email' ? 'email' : ($type === 'phone' ? 'tel' : 'text') }}"
                                           wire:model="answers.{{ $key }}"
                                           placeholder="{{ $field['placeholder'] ?? '' }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200" />
                            @endswitch

                            @error("answers.{$key}")
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <div class="pt-4">
                        <button type="submit" wire:loading.attr="disabled"
                                class="w-full py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-50 transition-colors">
                            <span wire:loading>Submitting...</span>
                            <span wire:loading.remove>Submit Response</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
