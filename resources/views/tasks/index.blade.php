{{-- resources/views/tasks/index.blade.php --}}

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

                    {{-- フラッシュメッセージなどを後でここに出しても良い --}}

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
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    <tr>
                                        <td class="px-4 py-2 border-b">
                                            {{ $task->title }}
                                        </td>
                                        <td class="px-4 py-2 border-b">
                                            {{ $task->status }}
                                        </td>
                                        <td class="px-4 py-2 border-b">
                                            {{ $task->priority }}
                                        </td>
                                        <td class="px-4 py-2 border-b">
                                            {{ optional($task->due_date)->format('Y-m-d') ?? '-' }}
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
