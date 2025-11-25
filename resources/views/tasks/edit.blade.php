<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Task') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- エラー表示 --}}
                    @if ($errors->any())
                        <div class="mb-4 text-red-600">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- 編集フォーム --}}
                    <form method="POST" action="{{ route('tasks.update', $task->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-sm font-medium">タイトル</label>
                            <input type="text" name="title" class="border rounded w-full p-2"
                                value="{{ old('title', $task->title) }}">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium">詳細</label>
                            <textarea name="description" class="border rounded w-full p-2" rows="4">{{ old('description', $task->description) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium">ステータス</label>
                            <select name="status" class="border rounded w-full p-2">
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('status', $task->status) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium">優先度</label>
                            <select name="priority" class="border rounded w-full p-2">
                                <option value="3" {{ old('priority', $task->priority) == 3 ? 'selected' : '' }}>高
                                </option>
                                <option value="2" {{ old('priority', $task->priority) == 2 ? 'selected' : '' }}>中
                                </option>
                                <option value="1" {{ old('priority', $task->priority) == 1 ? 'selected' : '' }}>低
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium">期限</label>
                            <input type="date" name="due_date" class="border rounded w-full p-2"
                                value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium">タグ</label>

                            @php
                                $selectedTagIds = old('tags', $task->tags->pluck('id')->toArray());
                            @endphp

                            <select name="tags[]" multiple class="border rounded w-full p-2">
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}"
                                        {{ in_array($tag->id, $selectedTagIds) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="text-xs text-gray-500 mt-1">
                                Ctrl / Cmd を押しながらクリックで複数選択できます。
                            </p>
                        </div>

                        <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-blue-700">
                            更新する
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
