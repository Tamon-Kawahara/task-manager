<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('タスク一覧') }}
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
                        {{-- ======================
                             検索フォーム
                           ======================= --}}
                        <div class="mb-4">
                            <form method="GET" action="{{ route('tasks.index') }}"
                                class="w-full flex flex-col gap-2 md:flex-row md:flex-wrap md:items-center md:justify-between">

                                {{-- キーワード --}}
                                <input type="text" name="keyword" placeholder="タイトルで検索"
                                    value="{{ request('keyword') }}" class="border rounded px-3 py-1 w-full md:w-64">

                                {{-- セレクト群 --}}
                                <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-2 w-full md:w-auto">
                                    <select name="status" class="border rounded px-2 py-1 pr-8 w-full md:w-auto">
                                        <option value="">ステータスを選択</option>
                                        <option value="not_started"
                                            {{ request('status') === 'not_started' ? 'selected' : '' }}>未着手</option>
                                        <option value="in_progress"
                                            {{ request('status') === 'in_progress' ? 'selected' : '' }}>進行中</option>
                                        <option value="completed"
                                            {{ request('status') === 'completed' ? 'selected' : '' }}>完了</option>
                                    </select>

                                    <select name="priority" class="border rounded px-2 py-1 pr-8 w-full md:w-auto">
                                        <option value="">優先度</option>
                                        <option value="1" {{ request('priority') === '1' ? 'selected' : '' }}>低
                                        </option>
                                        <option value="2" {{ request('priority') === '2' ? 'selected' : '' }}>中
                                        </option>
                                        <option value="3" {{ request('priority') === '3' ? 'selected' : '' }}>高
                                        </option>
                                    </select>

                                    <select name="tag_id" class="border rounded px-2 py-1 pr-8 w-full md:w-auto">
                                        <option value="">タグ</option>
                                        @foreach ($tags as $tag)
                                            <option value="{{ $tag->id }}"
                                                {{ (int) ($tagId ?? 0) === $tag->id ? 'selected' : '' }}>
                                                {{ $tag->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select name="sort" class="border rounded px-2 py-1 pr-8 w-full md:w-auto">
                                        <option value="">デフォルト</option>
                                        <option value="due_asc" {{ ($sort ?? '') === 'due_asc' ? 'selected' : '' }}>
                                            期限が近い順</option>
                                        <option value="due_desc" {{ ($sort ?? '') === 'due_desc' ? 'selected' : '' }}>
                                            期限が遠い順</option>
                                        <option value="priority_desc"
                                            {{ ($sort ?? '') === 'priority_desc' ? 'selected' : '' }}>優先度が高い順</option>
                                        <option value="custom" {{ ($sort ?? '') === 'custom' ? 'selected' : '' }}>
                                            カスタム順（ドラッグ）</option>
                                    </select>
                                </div>

                                {{-- チェックボックス＋検索 --}}
                                <div class="flex items-center justify-between gap-2 w-full md:w-auto">
                                    <label class="flex items-center text-sm text-gray-700">
                                        <input type="checkbox" name="hide_completed" value="1"
                                            class="mr-1 rounded border-gray-300"
                                            {{ request('hide_completed') ? 'checked' : '' }}>
                                        完了タスクを非表示
                                    </label>

                                    <button class="px-4 py-1 bg-blue-600 text-white rounded">検索</button>
                                </div>
                            </form>
                        </div>

                        <div class="mb-2 flex justify-end">
                            <a href="{{ route('tasks.archive') }}"
                                class="inline-flex items-center px-3 py-1 text-sm border rounded text-gray-700 hover:bg-gray-50">
                                アーカイブされたタスクを見る
                            </a>
                        </div>

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

                            $priorityLabels = [1 => '低', 2 => '中', 3 => '高'];
                        @endphp

                        {{-- ======================
                             スマホ & タブレット（〜1023px）カード一覧
                           ======================= --}}
                        <div class="lg:hidden flex flex-col gap-4">
                            @foreach ($tasks as $task)
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
                                            $dueDate->between($today->copy()->addDay(), $today->copy()->addDays(3))
                                        ) {
                                            $dueClass = 'bg-orange-50 text-orange-700';
                                        }
                                    }
                                @endphp

                                <div class="bg-white border rounded-lg p-4 shadow-sm">

                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <h3 class="font-semibold text-gray-900 text-sm leading-snug">
                                            {{ $task->title }}
                                        </h3>

                                        <form method="POST" action="{{ route('tasks.updateStatus', $task->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status"
                                                value="{{ $nextStatusMap[$task->status] }}">
                                            <button
                                                class="px-2 py-1 rounded text-xs {{ $statusColors[$task->status] }}">
                                                {{ $task->status_label }}
                                            </button>
                                        </form>
                                    </div>

                                    <div class="text-xs text-gray-700 mb-2 space-y-1">
                                        <div>優先度：{{ $priorityLabels[$task->priority] }}</div>
                                        <div>
                                            期限：
                                            <span class="px-1.5 py-0.5 rounded {{ $dueClass }}">
                                                {{ $dueDate ? $dueDate->format('Y-m-d') : '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        @forelse ($task->tags as $tag)
                                            <span
                                                class="inline-block px-2 py-0.5 text-xs rounded bg-gray-200 mr-1 mb-1">
                                                {{ $tag->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400">タグなし</span>
                                        @endforelse
                                    </div>

                                    <div class="flex gap-2">
                                        <a href="{{ route('tasks.edit', $task->id) }}"
                                            class="flex-1 text-center border rounded py-1 text-xs hover:bg-gray-100">
                                            編集
                                        </a>

                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                                            class="flex-1" onsubmit="return confirm('本当に削除しますか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="w-full text-center border rounded py-1 text-xs text-red-600 hover:bg-red-50">
                                                削除
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- ======================
                             PC版テーブル（1024px〜）
                           ======================= --}}
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 border-b w-8"></th>
                                        <th class="px-4 py-2 border-b">タイトル</th>
                                        <th class="px-4 py-2 border-b">ステータス</th>
                                        <th class="px-4 py-2 border-b">優先度</th>
                                        <th class="px-4 py-2 border-b">期限</th>
                                        <th class="px-4 py-2 border-b">タグ</th>
                                        <th class="px-4 py-2 border-b"></th>
                                        <th class="px-4 py-2 border-b"></th>
                                    </tr>
                                </thead>
                                <tbody id="task-table-body">
                                    @foreach ($tasks as $task)
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

                                        <tr data-task-id="{{ $task->id }}">

                                            <td
                                                class="px-2 py-2 border-b text-lg
                                            {{ ($sort ?? '') === 'custom' ? 'cursor-move text-gray-400 drag-handle' : 'text-gray-200' }}">
                                                @if (($sort ?? '') === 'custom')
                                                    ☰
                                                @endif
                                            </td>

                                            <td class="px-4 py-2 border-b">
                                                {{ $task->title }}
                                            </td>

                                            <td class="px-4 py-2 border-b">
                                                <form method="POST"
                                                    action="{{ route('tasks.updateStatus', $task->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status"
                                                        value="{{ $nextStatusMap[$task->status] }}">
                                                    <button
                                                        class="px-2 py-1 rounded text-xs {{ $statusColors[$task->status] }}">
                                                        {{ $task->status_label }}
                                                    </button>
                                                </form>
                                            </td>

                                            <td class="px-4 py-2 border-b">
                                                {{ $priorityLabels[$task->priority] }}
                                            </td>

                                            <td class="px-4 py-2 border-b">
                                                <span class="px-2 py-1 rounded {{ $dueClass }}">
                                                    {{ $dueDate ? $dueDate->format('Y-m-d') : '-' }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-2 border-b">
                                                @forelse ($task->tags as $tag)
                                                    <span
                                                        class="inline-block px-2 py-0.5 text-xs rounded bg-gray-200 mr-1">
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
                                                    class="inline-block ml-2"
                                                    onsubmit="return confirm('本当に削除しますか？');">
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
                        </div>

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

            if (sortSelect) {
                sortSelect.addEventListener('change', () => {
                    sortSelect.form.submit();
                });
            }

            if (!tbody || !sortSelect) return;

            if (sortSelect.value !== 'custom') return;

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
                        if (!res.ok) {
                            console.error('Failed to save order');
                        }
                    }).catch(err => console.error(err));
                }
            });
        });
    </script>

</x-app-layout>
