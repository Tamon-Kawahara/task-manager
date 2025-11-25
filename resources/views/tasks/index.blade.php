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
                                <select name="status" class="border rounded px-2 py-1 pr-8">
                                    <option value="">ステータスを選択</option>
                                    <option value="not_started"
                                        {{ request('status') === 'not_started' ? 'selected' : '' }}>未着手</option>
                                    <option value="in_progress"
                                        {{ request('status') === 'in_progress' ? 'selected' : '' }}>進行中</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                                        完了</option>
                                </select>
                                {{-- ★ 優先度絞り込みを追加 --}}
                                <select name="priority" class="border rounded px-2 py-1 pr-8">
                                    <option value="">優先度</option>
                                    <option value="1" {{ request('priority') === '1' ? 'selected' : '' }}>低
                                    </option>
                                    <option value="2" {{ request('priority') === '2' ? 'selected' : '' }}>中
                                    </option>
                                    <option value="3" {{ request('priority') === '3' ? 'selected' : '' }}>高
                                    </option>
                                </select>

                                {{-- ★ タグ絞り込みを追加 --}}
                                <select name="tag_id" class="border rounded px-2 py-1 pr-8">
                                    <option value="">タグ</option>
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag->id }}"
                                            {{ (int) ($tagId ?? 0) === $tag->id ? 'selected' : '' }}>
                                            {{ $tag->name }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- ★ 並び替えを追加 --}}
                                <select name="sort" class="border rounded px-2 py-1 pr-8">
                                    <option value="">並び替え</option>
                                    <option value="due_asc" {{ ($sort ?? '') === 'due_asc' ? 'selected' : '' }}>期限が近い順
                                    </option>
                                    <option value="due_desc" {{ ($sort ?? '') === 'due_desc' ? 'selected' : '' }}>
                                        期限が遠い順</option>
                                    <option value="priority_desc"
                                        {{ ($sort ?? '') === 'priority_desc' ? 'selected' : '' }}>優先度が高い順</option>
                                    <option value="custom" {{ ($sort ?? '') === 'custom' ? 'selected' : '' }}>
                                        カスタム順（ドラッグ）</option>
                                </select>
                                </select>

                                <button class="px-4 py-1 bg-blue-600 text-white rounded">検索</button>
                            </form>
                        </div>

                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 border-b w-8"></th> {{-- ドラッグハンドル --}}
                                    <th class="px-4 py-2 border-b">タイトル</th>
                                    <th class="px-4 py-2 border-b">ステータス</th>
                                    <th class="px-4 py-2 border-b">優先度</th>
                                    <th class="px-4 py-2 border-b">期限</th>
                                    <th class="px-4 py-2 border-b">タグ</th>
                                    <th class="px-4 py-2 border-b"></th>
                                    <th class="px-4 py-2 border-b"></th>
                                </tr>
                            </thead>
                            <tbody id="task-table-body"> {{-- ★ ← ここに id を追加！！ --}}

                                @php
                                    $statusColors = [
                                        \App\Models\Task::STATUS_NOT_STARTED => 'bg-gray-200 text-gray-800',
                                        \App\Models\Task::STATUS_IN_PROGRESS => 'bg-blue-200 text-blue-800',
                                        \App\Models\Task::STATUS_COMPLETED => 'bg-green-200 text-green-800',
                                    ];

                                    $nextStatusMap = [
                                        \App\Models\Task::STATUS_NOT_STARTED => \App\Models\Task::STATUS_IN_PROGRESS,
                                        \App\Models\Task::STATUS_IN_PROGRESS => \App\Models\Task::STATUS_COMPLETED,
                                        \App\Models\Task::STATUS_COMPLETED => \App\Models\Task::STATUS_NOT_STARTED,
                                    ];

                                    $priorityLabels = [
                                        1 => '低',
                                        2 => '中',
                                        3 => '高',
                                    ];
                                @endphp

                                @foreach ($tasks as $task)
                                    {{-- ★ 各行に「data-task-id」追加 --}}
                                    <tr data-task-id="{{ $task->id }}">

                                        {{-- ★ ドラッグハンドル列を追加（必ず1列目に入れる） --}}
                                        <td class="px-2 py-2 border-b cursor-move text-gray-400 text-lg drag-handle">
                                            ☰
                                        </td>

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

                                                <button type="submit"
                                                    class="px-2 py-1 rounded text-xs {{ $statusClass }}">
                                                    {{ $task->status_label }}
                                                </button>
                                            </form>
                                        </td>

                                        <td class="px-4 py-2 border-b">
                                            {{ $priorityLabels[$task->priority] ?? '-' }}
                                        </td>

                                        @php
                                            $dueDate = $task->due_date;
                                            $today = \Carbon\Carbon::today();

                                            $dueClass = '';

                                            if ($dueDate) {
                                                if ($dueDate->lt($today)) {
                                                    $dueClass = 'bg-red-50 text-red-700';
                                                } elseif ($dueDate->equalTo($today)) {
                                                    $dueClass = 'bg-yellow-50 text-yellow-700';
                                                } elseif (
                                                    $dueDate->between(
                                                        $today->copy()->addDay(),
                                                        $today->copy()->addDays(3),
                                                    )
                                                ) {
                                                    $dueClass = 'bg-orange-50 text-orange-700';
                                                }
                                            }
                                        @endphp

                                        <td class="px-4 py-2 border-b">
                                            <span class="px-2 py-1 rounded {{ $dueClass }}">
                                                {{ $dueDate ? $dueDate->format('Y-m-d') : '-' }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2 border-b">
                                            @forelse ($task->tags as $tag)
                                                <span class="inline-block px-2 py-0.5 text-xs rounded bg-gray-200 mr-1">
                                                    {{ $tag->name }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-gray-400">なし</span>
                                            @endforelse
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

                            </tbody> {{-- ★ id="task-table-body" の閉じタグ --}}

                        </table>

                        <div class="mt-4">
                            {{ $tasks->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tbody = document.getElementById('task-table-body');
            const sortSelect = document.querySelector('select[name="sort"]');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (!tbody || !sortSelect) return;

            // 「カスタム順」のときだけドラッグ有効
            if (sortSelect.value !== 'custom') {
                return;
            }

            new Sortable(tbody, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function() {
                    const order = Array.from(tbody.querySelectorAll('tr'))
                        .map(row => row.dataset.taskId);

                    fetch('{{ route('tasks.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            order
                        }),
                    }).then(res => {
                        // 失敗してたらコンソールに出すくらいはしておく
                        if (!res.ok) {
                            console.error('Failed to save order');
                        }
                    }).catch(err => {
                        console.error(err);
                    });
                }
            });
        });
    </script>

</x-app-layout>
