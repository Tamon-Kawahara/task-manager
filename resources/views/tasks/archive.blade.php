<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            アーカイブされたタスク
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- フラッシュメッセージ --}}
            @if (session('success'))
                <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 px-4 py-2 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- 戻るリンク --}}
            <div class="mb-4">
                <a href="{{ route('tasks.index') }}"
                    class="inline-flex items-center text-sm text-blue-600 hover:underline">
                    <span class="mr-1">←</span>タスク一覧に戻る
                </a>
            </div>

            @if ($archivedTasks->isEmpty())
                {{-- アーカイブが0件のとき --}}
                <div class="bg-white border border-gray-100 rounded-lg px-4 py-6 text-center shadow-sm">
                    <p class="text-sm text-gray-500">
                        アーカイブされたタスクはまだありません。
                    </p>
                </div>
            @else
                {{-- アーカイブ済みタスク一覧（レスポンシブカード） --}}
                <div class="space-y-3">
                    @foreach ($archivedTasks as $task)
                        <div
                            class="bg-white border border-gray-100 rounded-lg px-4 py-3 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                            {{-- タスク情報 --}}
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">
                                    {{ $task->title }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    削除日：{{ optional($task->deleted_at)->format('Y-m-d H:i') }}
                                </p>
                            </div>

                            {{-- 復元ボタン --}}
                            <div class="flex justify-end sm:justify-start">
                                <form method="POST" action="{{ route('tasks.restore', $task->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="px-3 py-1.5 text-xs font-medium rounded border border-blue-600 text-blue-600 hover:bg-blue-50">
                                        復元する
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
