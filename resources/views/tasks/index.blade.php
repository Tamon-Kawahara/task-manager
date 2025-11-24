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
                                            {{ $task->status }}
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
