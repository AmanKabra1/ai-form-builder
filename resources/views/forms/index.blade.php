<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Forms</h2>
            <div class="flex gap-2">
                <a href="{{ route('forms.import') }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    ↑ Import
                </a>
                <a href="{{ route('forms.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                    + New Form
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            @if($forms->isEmpty())
                <div class="text-center py-20 bg-white rounded-xl shadow">
                    <div class="text-6xl mb-4">📋</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No forms yet</h3>
                    <p class="text-gray-500 mb-6">Create your first form manually or generate one with AI.</p>
                    <a href="{{ route('forms.create') }}"
                       class="inline-flex items-center px-6 py-3 bg-indigo-600 rounded-lg text-white font-medium hover:bg-indigo-700">
                        + Create Form
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($forms as $form)
                        <div class="bg-white rounded-xl shadow hover:shadow-md transition-shadow p-6 flex flex-col">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0 mr-2">
                                    <h3 class="font-semibold text-gray-900 truncate">{{ $form->title }}</h3>
                                    @if($form->description)
                                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $form->description }}</p>
                                    @endif
                                </div>
                                <span class="shrink-0 px-2 py-1 text-xs rounded-full font-medium
                                    {{ $form->status === 'published' ? 'bg-green-100 text-green-700' : ($form->status === 'closed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                    {{ ucfirst($form->status) }}
                                </span>
                            </div>

                            <div class="flex gap-4 text-sm text-gray-500 mb-4">
                                <span>{{ count($form->fields) }} fields</span>
                                <span>{{ $form->submissions_count }} responses</span>
                                <span>{{ $form->updated_at->diffForHumans() }}</span>
                            </div>

                            <div class="mt-auto pt-4 border-t border-gray-100 flex items-center gap-2 flex-wrap">
                                <a href="{{ route('forms.edit', $form->id) }}"
                                   class="px-3 py-1.5 text-sm bg-indigo-50 text-indigo-700 rounded-md hover:bg-indigo-100 font-medium">
                                    Edit
                                </a>
                                @if($form->status === 'published')
                                    <a href="{{ route('forms.fill', $form->slug) }}" target="_blank"
                                       class="px-3 py-1.5 text-sm bg-green-50 text-green-700 rounded-md hover:bg-green-100 font-medium">
                                        View
                                    </a>
                                    <a href="{{ route('forms.submissions', $form->id) }}"
                                       class="px-3 py-1.5 text-sm bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 font-medium">
                                        Responses ({{ $form->submissions_count }})
                                    </a>
                                @endif
                                <form action="{{ route('forms.destroy', $form->id) }}" method="POST" class="ml-auto"
                                      onsubmit="return confirm('Delete this form?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-md">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">{{ $forms->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
