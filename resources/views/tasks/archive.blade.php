<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            アーカイブされたタスク
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- フラッシュメッセージ --}}
            @if (session('success'))
                <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 px-4 py-2 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- 戻るリンク --}}
            <div class="mb-4">
                <a href="{{ route('tasks.index') }}"
                   class="text-sm text-blue-600 hover:underline">
                    ← タスク一覧に戻る
                </a>
            </div>

            @if ($archivedTasks->isEmpty())
                {{-- アーカイブが0件のとき --}}
                <p class="text-sm text-gray-500">
                    アーカイブされたタスクはありません。
                </p>
            @else
                {{-- アーカイブ済みタスク一覧 --}}
                <div class="space-y-2">
                    @foreach ($archivedTasks as $task)
                        <div class="flex items-center justify-between bg-white border border-gray-100 rounded-lg px-4 py-3 shadow-sm">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $task->title }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    削除日：{{ optional($task->deleted_at)->format('Y-m-d H:i') }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                {{-- 復元ボタン --}}
                                <form method="POST" action="{{ route('tasks.restore', $task->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="px-3 py-1 text-xs font-medium rounded border border-blue-600 text-blue-600 hover:bg-blue-50">
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
