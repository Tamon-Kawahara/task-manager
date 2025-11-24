<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tasks') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('success'))
                        <div class="mb-4 text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($tasks->count() === 0)
                        <p>まだタスクがありません。</p>
                    @else
                        <div class="mb-4 flex justify-end">
                            <form method="GET" action="{{ route('tasks.index') }}" class="flex gap-2">
                                <input type="text" name="keyword" placeholder="タイトルで検索"
                                    value="{{ request('keyword') }}" class="border rounded px-3 py-1 w-64">
                                <select name="status" class="border rounded px-2 py-1">
                                    <option value="">ステータスを選択</option>
                                    <option value="not_started"
                                        {{ request('status') === 'not_started' ? 'selected' : '' }}>未着手</option>
                                    <option value="in_progress"
                                        {{ request('status') === 'in_progress' ? 'selected' : '' }}>進行中</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                                        完了</option>
                                </select>

                                <button class="px-4 py-1 bg-blue-600 text-white rounded">検索</button>
                            </form>
                        </div>

                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 border-b">タイトル</th>
                                    <th class="px-4 py-2 border-b">ステータス</th>
                                    <th class="px-4 py-2 border-b">優先度</th>
                                    <th class="px-4 py-2 border-b">期限</th>
                                    <th class="px-4 py-2 border-b"></th>
                                    <th class="px-4 py-2 border-b"></th>
                                </tr>
                            </thead>
                            <tbody>

                                @php
                                    $statusColors = [
                                        \App\Models\Task::STATUS_NOT_STARTED => 'bg-gray-200 text-gray-800',
                                        \App\Models\Task::STATUS_IN_PROGRESS => 'bg-blue-200 text-blue-800',
                                        \App\Models\Task::STATUS_COMPLETED => 'bg-green-200 text-green-800',
                                    ];

                                    // 現在のステータス → 次のステータス
                                    $nextStatusMap = [
                                        \App\Models\Task::STATUS_NOT_STARTED => \App\Models\Task::STATUS_IN_PROGRESS, // 未着手 → 進行中
                                        \App\Models\Task::STATUS_IN_PROGRESS => \App\Models\Task::STATUS_COMPLETED, // 進行中 → 完了
                                        \App\Models\Task::STATUS_COMPLETED => \App\Models\Task::STATUS_NOT_STARTED, // 完了 → 未着手
                                    ];

                                    $priorityLabels = [
                                        1 => '低',
                                        2 => '中',
                                        3 => '高',
                                    ];
                                @endphp

                                @foreach ($tasks as $task)
                                    <tr>
                                        <td class="px-4 py-2 border-b">
                                            {{ $task->title }}
                                        </td>

                                        <td class="px-4 py-2 border-b">
                                            @php
                                                $statusClass =
                                                    $statusColors[$task->status] ?? 'bg-gray-100 text-gray-800';
                                                $nextStatus = $nextStatusMap[$task->status] ?? Task::STATUS_NOT_STARTED;
                                            @endphp

                                            <form method="POST" action="{{ route('tasks.updateStatus', $task->id) }}"
                                                class="inline">
                                                @csrf
                                                @method('PATCH')

                                                <input type="hidden" name="status" value="{{ $nextStatus }}">

                                                {{-- バッジ風ボタン：クリックすると次のステータスに更新 --}}
                                                <button type="submit"
                                                    class="px-2 py-1 rounded text-xs {{ $statusClass }}">
                                                    {{ $task->status_label }}
                                                </button>
                                            </form>
                                        </td>

                                        <td class="px-4 py-2 border-b">
                                            {{ $priorityLabels[$task->priority] ?? '-' }}
                                        </td>

                                        <td class="px-4 py-2 border-b">
                                            {{ optional($task->due_date)->format('Y-m-d') ?? '-' }}
                                        </td>
                                        <td class="px-4 py-2 border-b">
                                            <a href="{{ route('tasks.edit', $task->id) }}"
                                                class="inline-block px-3 py-1 border rounded text-sm hover:bg-gray-100">
                                                編集
                                            </a>
                                        </td>
                                        <td class="px-4 py-2 border-b">
                                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                                                class="inline-block ml-2" onsubmit="return confirm('本当に削除しますか？');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    class="px-3 py-1 border rounded text-sm text-red-600 hover:bg-red-50">
                                                    削除
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                        <div class="mt-4">
                            {{ $tasks->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
