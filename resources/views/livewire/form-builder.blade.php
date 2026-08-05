<div class="min-h-screen bg-gray-50">

    {{-- Top Bar --}}
    <div class="bg-white border-b px-6 py-3 flex items-center gap-4 sticky top-0 z-40 shadow-sm">
        <a href="{{ route('forms.index') }}" class="text-gray-400 hover:text-gray-600 shrink-0">← Back</a>

        <input type="text" wire:model.blur="title" placeholder="Form title..."
               class="flex-1 text-lg font-semibold border-0 focus:ring-0 bg-transparent px-2 min-w-0" />

        <select wire:model="status" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 shrink-0 pr-8 min-w-36">
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="closed">Closed</option>
        </select>

        <button wire:click="$toggle('showAiPanel')"
                class="px-3 py-1.5 text-sm bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 font-medium {{ $showAiPanel ? 'ring-2 ring-purple-300' : '' }}">
            AI Generate
        </button>

        <button wire:click="$toggle('showVersions')"
                class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 {{ $showVersions ? 'ring-2 ring-gray-300' : '' }}">
            Versions
        </button>

        <button wire:click="$toggle('showRawEditor')"
                class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 {{ $showRawEditor ? 'ring-2 ring-gray-300' : '' }}">
            JSON
        </button>

        <button wire:click="save" wire:loading.attr="disabled"
                class="px-4 py-1.5 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium disabled:opacity-50">
            <span wire:loading wire:target="save">Saving...</span>
            <span wire:loading.remove wire:target="save">Save</span>
        </button>

        @if($saveStatus)
            <span class="text-green-600 text-sm font-medium" x-data="{ show: true }"
                  x-init="setTimeout(() => show = false, 2000)" x-show="show">
                ✓ {{ $saveStatus }}
            </span>
        @endif
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mx-6 mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    {{-- AI Panel --}}
    @if($showAiPanel)
        <div class="bg-purple-50 border-b border-purple-200 px-6 py-4">
            <div class="max-w-2xl flex gap-3 items-start">
                <div class="flex-1">
                    <textarea wire:model="aiPrompt" rows="2" placeholder='e.g. "Job application with education history, skills and resume upload"'
                              class="w-full text-sm border border-purple-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-300 resize-none"></textarea>
                </div>
                <div class="flex flex-col gap-2">
                    <select wire:model="aiMode" class="text-sm border border-purple-200 rounded-lg px-2 py-1.5 pr-8 min-w-32">
                        <option value="create">Create new</option>
                        <option value="edit">Edit existing</option>
                    </select>
                    <button wire:click="generateWithAI" wire:loading.attr="disabled"
                            class="px-4 py-1.5 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50">
                        <span wire:loading wire:target="generateWithAI">Queuing...</span>
                        <span wire:loading.remove wire:target="generateWithAI">Generate</span>
                    </button>
                </div>
            </div>

            @if($aiJobStatus)
                <div class="mt-2 text-sm flex items-center gap-2"
                     x-data="{ poll: null }"
                     x-init="
                        if ('{{ $aiJobStatus }}' === 'queued' || '{{ $aiJobStatus }}' === 'processing') {
                            poll = setInterval(() => $wire.pollAiStatus(), 2000);
                        }
                        $watch('$wire.aiJobStatus', v => {
                            if (v === 'done' || v.startsWith('failed')) clearInterval(poll);
                        });
                     ">
                    @if($aiJobStatus === 'queued' || $aiJobStatus === 'processing')
                        <span class="animate-spin">⏳</span>
                        <span class="text-purple-700">AI is generating your form...</span>
                    @elseif($aiJobStatus === 'done')
                        <span class="text-green-700">✓ Form generated! Fields updated above.</span>
                    @elseif(str_starts_with($aiJobStatus, 'failed'))
                        <span class="text-red-600">✗ Generation failed. Check your API key.</span>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Versions Panel --}}
    @if($showVersions && $formId)
        <div class="bg-gray-50 border-b px-6 py-3">
            <div class="text-sm font-medium text-gray-700 mb-2">Version History</div>
            <div class="flex flex-wrap gap-2">
                @foreach($versions as $v)
                    <div class="flex items-center gap-2 bg-white border rounded-lg px-3 py-1.5 text-sm">
                        <span class="text-gray-700">v{{ $v->version_number }}</span>
                        <span class="text-gray-400 text-xs">{{ $v->created_at->diffForHumans() }}</span>
                        <button wire:click="rollbackTo({{ $v->id }})"
                                wire:confirm="Roll back to version {{ $v->version_number }}?"
                                class="text-indigo-600 hover:text-indigo-800 text-xs font-medium ml-1">
                            Restore
                        </button>
                    </div>
                @endforeach
                @if($versions->isEmpty())
                    <span class="text-gray-500 text-sm">No versions saved yet.</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Main Area --}}
    <div class="flex h-full" style="min-height: calc(100vh - 60px)">

        {{-- Canvas --}}
        <div class="flex-1 p-6 overflow-y-auto">

            {{-- Form Meta --}}
            <div class="max-w-2xl mx-auto mb-4">
                <textarea wire:model="description" rows="2" placeholder="Form description (optional)"
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-500 focus:ring-2 focus:ring-indigo-200 resize-none"></textarea>
            </div>

            {{-- Fields Canvas --}}
            <div class="max-w-2xl mx-auto">
                @if(empty($fields))
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-16 text-center text-gray-400">
                        <div class="text-4xl mb-3">📋</div>
                        <p class="font-medium">Click a field type on the right to add it</p>
                        <p class="text-sm mt-1">Or use AI to generate the form automatically</p>
                    </div>
                @else
                    <div id="fields-canvas" class="space-y-2">
                        @foreach($fields as $field)
                            <div data-field-id="{{ $field['id'] }}"
                                 wire:key="field-{{ $field['id'] }}"
                                 class="bg-white border rounded-xl p-4 hover:shadow-sm transition-shadow
                                        {{ $selectedFieldId === $field['id'] ? 'border-indigo-400 ring-2 ring-indigo-100' : 'border-gray-200' }}">

                                <div class="flex items-center gap-3">
                                    <span class="drag-handle text-gray-300 cursor-grab text-lg select-none hover:text-gray-500">⠿</span>

                                    <div class="flex-1 min-w-0">
                                        @if($field['type'] === 'heading')
                                            <div class="text-base font-bold text-gray-700">{{ $field['label'] }}</div>
                                            <div class="text-xs text-gray-400 mt-0.5">Section heading</div>
                                        @else
                                            <div class="text-sm font-medium text-gray-800">
                                                {{ $field['label'] }}
                                                @if($field['required'] ?? false)
                                                    <span class="text-red-500 ml-1">*</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-2 flex-wrap">
                                                <span class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-500 font-mono whitespace-nowrap shrink-0">{{ $field['type'] }}</span>
                                                @if(!empty($field['placeholder'])) <span class="truncate">{{ $field['placeholder'] }}</span> @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-0.5 shrink-0 ml-2">
                                        <button wire:click="selectField('{{ $field['id'] }}')"
                                                class="p-1.5 rounded hover:bg-gray-100 text-gray-400 hover:text-indigo-600 text-lg w-8 h-8 flex items-center justify-center" title="Edit">
                                            ✏️
                                        </button>
                                        <button wire:click="duplicateField('{{ $field['id'] }}')"
                                                class="p-1.5 rounded hover:bg-gray-100 text-gray-400 hover:text-gray-600 text-lg w-8 h-8 flex items-center justify-center" title="Duplicate">
                                            📋
                                        </button>
                                        <button wire:click="removeField('{{ $field['id'] }}')"
                                                wire:confirm="Remove this field?"
                                                class="p-1.5 rounded hover:bg-red-50 text-gray-400 hover:text-red-500 text-lg w-8 h-8 flex items-center justify-center" title="Delete">
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Public URL display --}}
                @if($slug && $status === 'published')
                    <div class="mt-6 p-3 bg-green-50 border border-green-200 rounded-lg text-sm flex items-center gap-2">
                        <span class="text-green-700 font-medium shrink-0">Live URL:</span>
                        <a href="{{ route('forms.fill', $slug) }}" target="_blank"
                           class="text-indigo-600 hover:underline truncate">
                            {{ route('forms.fill', $slug) }}
                        </a>
                    </div>
                @elseif($slug)
                    <div class="mt-6 p-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-500">
                        Set status to <strong>Published</strong> to share this form publicly.
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Panel --}}
        <div class="w-80 bg-white border-l flex flex-col overflow-y-auto">

            @if($selectedField)
                {{-- Field Config Panel --}}
                <div class="p-4 border-b flex items-center justify-between">
                    <span class="font-semibold text-sm text-gray-700">Field Settings</span>
                    <button wire:click="$set('selectedFieldId', null)" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div class="p-4 space-y-3 text-sm">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Label *</label>
                        <input type="text" value="{{ $selectedField['label'] }}"
                               wire:change="updateField('{{ $selectedField['id'] }}', 'label', $event.target.value)"
                               class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Key (snake_case)</label>
                        <input type="text" value="{{ $selectedField['key'] }}"
                               wire:change="updateField('{{ $selectedField['id'] }}', 'key', $event.target.value)"
                               class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm font-mono" />
                    </div>
                    @if($selectedField['type'] !== 'heading')
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Placeholder</label>
                            <input type="text" value="{{ $selectedField['placeholder'] ?? '' }}"
                                   wire:change="updateField('{{ $selectedField['id'] }}', 'placeholder', $event.target.value)"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Help Text</label>
                            <input type="text" value="{{ $selectedField['help_text'] ?? '' }}"
                                   wire:change="updateField('{{ $selectedField['id'] }}', 'help_text', $event.target.value)"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="req-{{ $selectedField['id'] }}"
                                   {{ ($selectedField['required'] ?? false) ? 'checked' : '' }}
                                   wire:change="updateField('{{ $selectedField['id'] }}', 'required', $event.target.checked)"
                                   class="rounded border-gray-300 text-indigo-600" />
                            <label for="req-{{ $selectedField['id'] }}" class="text-xs font-medium text-gray-600">Required field</label>
                        </div>

                        @if(in_array($selectedField['type'], ['text', 'textarea']))
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Min Length</label>
                                    <input type="number" value="{{ $selectedField['validation']['min_length'] ?? '' }}"
                                           wire:change="updateField('{{ $selectedField['id'] }}', 'validation.min_length', $event.target.value ?: null)"
                                           class="w-full border border-gray-200 rounded px-2 py-1 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Max Length</label>
                                    <input type="number" value="{{ $selectedField['validation']['max_length'] ?? '' }}"
                                           wire:change="updateField('{{ $selectedField['id'] }}', 'validation.max_length', $event.target.value ?: null)"
                                           class="w-full border border-gray-200 rounded px-2 py-1 text-sm" />
                                </div>
                            </div>
                        @endif

                        @if(in_array($selectedField['type'], ['dropdown', 'radio', 'checkbox']))
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Options</label>
                                <div class="space-y-1">
                                    @foreach($selectedField['options'] as $oi => $opt)
                                        <div class="flex gap-1.5 items-center">
                                            <input type="text" value="{{ $opt['label'] }}"
                                                   wire:change="updateField('{{ $selectedField['id'] }}', 'options.{{ $oi }}.label', $event.target.value)"
                                                   class="flex-1 border border-gray-200 rounded px-2 py-1 text-xs min-w-0" placeholder="Label" />
                                            <input type="text" value="{{ $opt['value'] }}"
                                                   wire:change="updateField('{{ $selectedField['id'] }}', 'options.{{ $oi }}.value', $event.target.value)"
                                                   class="w-20 border border-gray-200 rounded px-2 py-1 text-xs font-mono" placeholder="value" />
                                            <button wire:click="removeOption('{{ $selectedField['id'] }}', {{ $oi }})"
                                                    class="text-red-400 hover:text-red-600 px-1.5 py-1 rounded hover:bg-red-50 flex-shrink-0 w-6 h-6 flex items-center justify-center">✕</button>
                                        </div>
                                    @endforeach
                                </div>
                                <button wire:click="addOption('{{ $selectedField['id'] }}')"
                                        class="mt-2 text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                    + Add option
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            @else
                {{-- Field Palette --}}
                <div class="p-4 border-b">
                    <div class="font-semibold text-sm text-gray-700 mb-3">Add Fields</div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([
                            ['type' => 'text',     'icon' => 'T',  'label' => 'Text'],
                            ['type' => 'textarea', 'icon' => '¶',  'label' => 'Textarea'],
                            ['type' => 'number',   'icon' => '#',  'label' => 'Number'],
                            ['type' => 'email',    'icon' => '@',  'label' => 'Email'],
                            ['type' => 'phone',    'icon' => '📞', 'label' => 'Phone'],
                            ['type' => 'date',     'icon' => '📅', 'label' => 'Date'],
                            ['type' => 'dropdown', 'icon' => '▽',  'label' => 'Dropdown'],
                            ['type' => 'radio',    'icon' => '◉',  'label' => 'Radio'],
                            ['type' => 'checkbox', 'icon' => '☑',  'label' => 'Checkbox'],
                            ['type' => 'file',     'icon' => '📎',  'label' => 'File Upload'],
                            ['type' => 'rating',   'icon' => '★',  'label' => 'Rating'],
                            ['type' => 'heading',  'icon' => 'H',  'label' => 'Heading'],
                        ] as $ft)
                            <button wire:click="addField('{{ $ft['type'] }}')"
                                    class="flex items-center gap-1.5 px-2 py-2 border border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 text-left transition-colors min-w-0">
                                <span class="text-base w-5 h-5 flex items-center justify-center shrink-0 text-gray-500">{{ $ft['icon'] }}</span>
                                <span class="text-xs font-medium text-gray-700 truncate">{{ $ft['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                @if($formId)
                    <div class="p-4">
                        <a href="{{ route('forms.submissions', $formId) }}"
                           class="block w-full text-center py-2 text-sm text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">
                            View Responses
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Raw JSON Editor --}}
    @if($showRawEditor)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="font-semibold">Raw JSON Schema</h3>
                    <button wire:click="$toggle('showRawEditor')" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div class="flex-1 overflow-auto p-4">
                    @if($schemaErrors)
                        <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                            @foreach($schemaErrors as $err) <div>{{ $err }}</div> @endforeach
                        </div>
                    @endif
                    <textarea wire:model="rawSchemaJson" rows="20"
                              class="w-full font-mono text-xs border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-indigo-200"
                              style="min-height: 400px"></textarea>
                </div>
                <div class="p-4 border-t flex gap-2 justify-end">
                    <button wire:click="$toggle('showRawEditor')" class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button wire:click="applyRawSchema"
                            class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Apply Schema</button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
document.addEventListener('livewire:initialized', () => {
    initSortable();
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => { setTimeout(initSortable, 100); });
    });
});

function initSortable() {
    const el = document.getElementById('fields-canvas');
    if (!el || el._sortable) return;
    el._sortable = new Sortable(el, {
        animation: 150,
        handle: '.drag-handle',
        onEnd(evt) {
            const ids = [...el.querySelectorAll('[data-field-id]')].map(e => e.dataset.fieldId);
            Livewire.find(el.closest('[wire\\:id]')?.getAttribute('wire:id'))?.call('reorderFields', ids);
        }
    });
}
</script>
@endpush
