<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('タスク編集') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- エラー表示 --}}
                    @if ($errors->any())
                        <div class="mb-4 border border-red-200 bg-red-50 text-red-700 text-sm rounded p-3">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- 編集フォーム --}}
                    <form method="POST" action="{{ route('tasks.update', $task->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- タイトル --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                タイトル
                            </label>
                            <input type="text" name="title"
                                class="border rounded w-full px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                value="{{ old('title', $task->title) }}">
                        </div>

                        {{-- 詳細 --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                詳細
                            </label>
                            <textarea name="description" rows="4"
                                class="border rounded w-full px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', $task->description) }}</textarea>
                        </div>

                        {{-- ステータス / 優先度 / 期限（md 以上で3カラム） --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    ステータス
                                </label>
                                <select name="status"
                                    class="border rounded w-full px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    @foreach ($statusOptions as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('status', $task->status) === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    優先度
                                </label>
                                <select name="priority"
                                    class="border rounded w-full px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="3"
                                        {{ old('priority', $task->priority) == 3 ? 'selected' : '' }}>高</option>
                                    <option value="2"
                                        {{ old('priority', $task->priority) == 2 ? 'selected' : '' }}>中</option>
                                    <option value="1"
                                        {{ old('priority', $task->priority) == 1 ? 'selected' : '' }}>低</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    期限
                                </label>
                                <input type="date" name="due_date"
                                    class="border rounded w-full px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                    value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}">
                            </div>
                        </div>

                        {{-- タグ（複数選択） --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                タグ
                            </label>

                            @php
                                $selectedTagIds = old('tags', $task->tags->pluck('id')->toArray());
                            @endphp

                            <select name="tags[]" multiple
                                class="border rounded w-full px-3 py-2 text-sm h-32 md:h-40 focus:ring-blue-500 focus:border-blue-500">
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}"
                                        {{ in_array($tag->id, $selectedTagIds) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="text-xs text-gray-500 mt-1">
                                PC：Ctrl / Cmd キーを押しながらクリックで複数選択できます。<br class="hidden sm:inline">
                                スマホ：長押しやスクロールで複数項目を選択してください。
                            </p>
                        </div>

                        {{-- ボタン行 --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                            <button type="submit"
                                class="inline-flex justify-center items-center px-5 py-2.5 text-sm font-medium rounded bg-blue-600 text-white hover:bg-blue-700">
                                更新する
                            </button>

                            <a href="{{ route('tasks.index') }}"
                                class="inline-flex justify-center items-center px-5 py-2.5 text-sm font-medium rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
                                一覧に戻る
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
