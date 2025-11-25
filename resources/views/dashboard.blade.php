<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ダッシュボード
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- クイック操作 --}}
            <div class="grid gap-4 md:grid-cols-3">
                <div class="bg-white shadow-sm rounded-lg p-4 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">タスク一覧</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            登録済みのタスクを確認・編集します。
                        </p>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('tasks.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-gray-700 hover:bg-gray-800">
                            一覧を開く
                        </a>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">新規タスク</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            今日のやることをすぐに登録できます。
                        </p>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('tasks.create') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            タスクを追加
                        </a>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">タグ管理</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            タスクに紐づけるカテゴリやタグを編集します。
                        </p>
                    </div>
                    <div class="mt-3 space-x-2">
                        <a href="{{ route('tags.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 border border-gray-300 hover:bg-gray-50">
                            タグ登録
                        </a>
                    </div>
                </div>
            </div>

            {{-- 今日 & 今週のタスク --}}
            <div class="grid gap-6 lg:grid-cols-2">
                {{-- 今日のタスク --}}
                <div class="bg-white shadow-sm rounded-lg p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold text-gray-800">
                            今日のタスク
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                     {{ $todayTasks->count() ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $todayTasks->count() }} 件
                        </span>
                    </div>

                    @if ($todayTasks->isEmpty())
                        <p class="text-sm text-gray-500">
                            今日はタスクはありません。
                        </p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($todayTasks as $task)
                                <li class="flex items-center justify-between px-3 py-2 rounded border border-gray-100 hover:bg-gray-50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ $task->title }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            優先度：
                                            @if ($task->priority === 1) 高
                                            @elseif ($task->priority === 2) 中
                                            @else 低
                                            @endif
                                        </p>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded
                                                 @if($task->status === 'not_started') bg-gray-100 text-gray-700
                                                 @elseif($task->status === 'in_progress') bg-blue-100 text-blue-700
                                                 @else bg-green-100 text-green-700 @endif">
                                        {{ $task->status_label ?? 'ステータス' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- 今週のタスク --}}
                <div class="bg-white shadow-sm rounded-lg p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold text-gray-800">
                            今週のタスク
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                     {{ $weekTasks->count() ? 'bg-orange-50 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $weekTasks->count() }} 件
                        </span>
                    </div>

                    @if ($weekTasks->isEmpty())
                        <p class="text-sm text-gray-500">
                            今週のタスクはありません。
                        </p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($weekTasks as $task)
                                <li class="flex items-center justify-between px-3 py-2 rounded border border-gray-100 hover:bg-gray-50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ $task->title }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            期限：{{ optional($task->due_date)->format('Y-m-d') ?? '-' }}
                                        </p>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded
                                                 @if($task->status === 'not_started') bg-gray-100 text-gray-700
                                                 @elseif($task->status === 'in_progress') bg-blue-100 text-blue-700
                                                 @else bg-green-100 text-green-700 @endif">
                                        {{ $task->status_label ?? 'ステータス' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- ステータス状況（ミニボード） --}}
            <div class="bg-white shadow-sm rounded-lg p-5">
                <h3 class="text-base font-semibold text-gray-800 mb-4">
                    ステータス状況
                </h3>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="border rounded-lg p-3 flex flex-col">
                        <span class="text-xs text-gray-500 mb-1">未着手</span>
                        <span class="text-2xl font-semibold text-gray-800">
                            {{ $statusCounts['not_started'] ?? 0 }}
                        </span>
                    </div>
                    <div class="border rounded-lg p-3 flex flex-col">
                        <span class="text-xs text-gray-500 mb-1">進行中</span>
                        <span class="text-2xl font-semibold text-blue-700">
                            {{ $statusCounts['in_progress'] ?? 0 }}
                        </span>
                    </div>
                    <div class="border rounded-lg p-3 flex flex-col">
                        <span class="text-xs text-gray-500 mb-1">完了</span>
                        <span class="text-2xl font-semibold text-green-700">
                            {{ $statusCounts['completed'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
