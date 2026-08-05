<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('forms.index') }}" class="text-indigo-600 hover:underline text-sm">← Forms</a>
                <h2 class="font-semibold text-xl text-gray-800 mt-1">{{ $form->title }} — Responses</h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('forms.edit', $form->id) }}"
                   class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Edit Form</a>
                <a href="{{ route('forms.export', $form->id) }}"
                   class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">↓ Export CSV</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Search bar --}}
            <div class="mb-4 flex gap-3 items-center flex-wrap">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search responses..."
                       class="flex-1 min-w-64 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-200" />
                <select wire:model.live="perPage" class="border border-gray-300 rounded-lg px-3 py-2 text-sm shrink-0 pr-8 min-w-16">
                    <option>15</option>
                    <option>30</option>
                    <option>50</option>
                </select>
                <span class="text-sm text-gray-500 shrink-0">{{ $submissions->total() }} total</span>
            </div>

            @if($submissions->isEmpty())
                <div class="bg-white rounded-xl shadow p-16 text-center text-gray-400">
                    <div class="text-4xl mb-3">📭</div>
                    <p class="font-medium">No responses yet</p>
                    @if($search)
                        <p class="text-sm mt-1">No results for "{{ $search }}"</p>
                    @endif
                </div>
            @else
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                                    @foreach($form->fields as $field)
                                        @if($field['type'] !== 'heading')
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase max-w-xs truncate" title="{{ $field['label'] }}">
                                                {{ $field['label'] }}
                                            </th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($submissions as $sub)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-400">{{ $sub->id }}</td>
                                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                            {{ $sub->created_at->format('d M Y, H:i') }}
                                        </td>
                                        @foreach($form->fields as $field)
                                            @if($field['type'] !== 'heading')
                                                <td class="px-4 py-3 text-gray-700 max-w-xs overflow-hidden text-ellipsis whitespace-nowrap" title="{{ is_array($sub->data[$field['key']] ?? null) ? implode(', ', $sub->data[$field['key']]) : ($sub->data[$field['key']] ?? '') }}">
                                                    {{ is_array($sub->data[$field['key']] ?? null)
                                                        ? implode(', ', $sub->data[$field['key']])
                                                        : ($sub->data[$field['key']] ?? '—') }}
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 border-t">{{ $submissions->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
